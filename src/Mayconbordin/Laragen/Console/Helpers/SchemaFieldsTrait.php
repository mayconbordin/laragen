<?php namespace Mayconbordin\Laragen\Console\Helpers;

use Mayconbordin\Laragen\Database\SchemaGenerator;
use Mayconbordin\Laragen\Exceptions\MethodNotFoundException;
use Mayconbordin\Laragen\Generator\MigrationGenerator;
use Mayconbordin\Laragen\Parsers\NameParser;
use Mayconbordin\Laragen\Parsers\SchemaParser;
use Mayconbordin\Laragen\Schema\Table;

trait SchemaFieldsTrait
{
    /**
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

    /**
     * Get a table object from the command line arguments (name and fields).
     *
     * @return Table
     */
    protected function fetchTableFromCli()
    {
        $meta   = (new NameParser())->parse($this->argument('name'));
        $fields = (new SchemaParser())->parse($this->option('fields'));

        return new Table($meta['table'], $fields);;
    }

    /**
     * Get the action name from the command line arguments (name or action).
     *
     * @return string|null
     */
    protected function fetchActionFromCli()
    {
        if ($this->option('action')) {
            return $this->option('action');
        }

        $meta = (new NameParser())->parse($this->argument('name'));
        return $meta['action'];
    }

    /**
     * Get a list of table objects from the database connection.
     *
     * @throws MethodNotFoundException
     */
    protected function fetchSchemaFromDb()
    {
        $this->schemaGenerator = new SchemaGenerator(
            $this->option('connection'),
            $this->option('default-index-names'),
            $this->option('default-fk-names')
        );

        if ($this->option('tables')) {
            $tables = explode(',', $this->option('tables'));
        } else {
            $tables = $this->schemaGenerator->getTables();
        }

        $tables = $this->removeExcludedTables($tables);
        return $this->schemaGenerator->getSchema($tables);
    }

    /**
     * Remove all the tables to exclude from the array of tables
     *
     * @param $tables
     *
     * @return array
     */
    protected function removeExcludedTables($tables)
    {
        $excludes = $this->getExcludedTables();
        $tables = array_diff($tables, $excludes);
        return $tables;
    }
    /**
     * Get a list of tables to exclude
     *
     * @return array
     */
    protected function getExcludedTables()
    {
        $excludes = ['migrations'];
        $ignore = $this->option('ignore');

        if (!empty($ignore)) {
            return array_merge($excludes, explode(',', $ignore));
        }

        return $excludes;
    }
}