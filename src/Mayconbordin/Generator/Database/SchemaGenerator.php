<?php namespace Mayconbordin\Generator\Database;

use \DB;
use Mayconbordin\Generator\Schema\Foreign;
use Mayconbordin\Generator\Schema\Table;

class SchemaGenerator
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schema;

    /**
     * @var FieldGenerator
     */
    protected $fieldGenerator;

    /**
     * @var ForeignKeyGenerator
     */
    protected $foreignKeyGenerator;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var bool
     */
    private $ignoreIndexNames;

    /**
     * @var bool
     */
    private $ignoreForeignKeyNames;

    /**
     * @param string $database
     * @param bool   $ignoreIndexNames
     * @param bool   $ignoreForeignKeyNames
     */
    public function __construct($database, $ignoreIndexNames, $ignoreForeignKeyNames)
    {
        $connection = DB::connection($database)->getDoctrineConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        $this->database = $connection->getDatabase();

        $this->schema = $connection->getSchemaManager();
        $this->fieldGenerator      = new FieldGenerator();
        $this->foreignKeyGenerator = new ForeignKeyGenerator();

        $this->ignoreIndexNames      = $ignoreIndexNames;
        $this->ignoreForeignKeyNames = $ignoreForeignKeyNames;
    }

    public function getSchema($tables = null)
    {
        if ($tables == null) {
            $tables = $this->getTables();
        }

        $schema = [];

        foreach ($tables as $tableName) {
            $fields = $this->getFields($tableName);
            $table  = new Table($tableName, $fields);

            foreach ($this->getForeignKeyConstraints($tableName) as $fk) {
                $field = $table->getField($fk['field']);

                if ($field != null) {
                    $field->setForeign($fk['on'], $fk['references'], $fk['name']);
                }
            }

            $schema[] = $table;
        }

        return $schema;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->schema->listTableNames();
    }

    /**
     * @param $table
     * @return array|bool
     */
    public function getFields($table)
    {
        return $this->fieldGenerator->generate($table, $this->schema, $this->database, $this->ignoreIndexNames);
    }

    public function getForeignKeyConstraints($table)
    {
        return $this->foreignKeyGenerator->generate($table, $this->schema, $this->ignoreForeignKeyNames);
    }
}