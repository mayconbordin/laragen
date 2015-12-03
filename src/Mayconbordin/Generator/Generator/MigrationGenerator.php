<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Schema\Field;
use Mayconbordin\Generator\Schema\Table;

class MigrationGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'migration/plain';

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var bool
     */
    protected $generateForeign;

    /**
     * @var bool
     */
    protected $onlyForeign;

    /**
     * @var string|null
     */
    protected $rawName;

    /**
     * MigrationGenerator constructor.
     *
     * @param array $options [ action=The name of the action being performed;
     *                         table=The table object with its fields;
     *                         generate_foreign=If the foreign keys should be generated;
     *                         only_foreign=If only the foreign keys should be generated ]
     */
    public function __construct(array $options = array())
    {
        parent::__construct('migration', $options);

        $this->table           = $options['table'];
        $this->generateForeign = array_get($options, 'generate_foreign', true);
        $this->onlyForeign     = array_get($options, 'only_foreign', false);
        $this->rawName         = array_get($options, 'raw_name', null);
    }

    /**
     * Get migration name.
     *
     * @return string
     */
    public function getMigrationName()
    {
        return strtolower($this->name);
    }

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName()
    {
        if ($this->rawName != null) {
            return strtolower($this->rawName);
        }

        return date('Y_m_d_His_').$this->getMigrationName();
    }

    /**
     * Get stub templates.
     *
     * @return string
     */
    public function getStub()
    {
        if ($this->action == 'create') {
            return Stub::createFromPath(__DIR__.'/../Stubs/migration/create.stub', [
                'class'  => $this->getClass(),
                'table'  => $this->table->getName(),
                'fields' => $this->constructSchema(),
            ]);
        } elseif ($this->action == 'create_simple') {
            return Stub::createFromPath(__DIR__.'/../Stubs/migration/create_simple.stub', [
                'class'  => $this->getClass(),
                'table'  => $this->table->getName(),
                'fields' => $this->constructSchema(),
            ]);
        } elseif ($this->action == 'add') {
            return Stub::createFromPath(__DIR__.'/../Stubs/migration/add.stub', [
                'class'       => $this->getClass(),
                'table'       => $this->table->getName(),
                'fields_up'   => $this->constructSchema(),
                'fields_down' => $this->constructSchema('drop'),
            ]);
        } elseif ($this->action == 'delete') {
            return Stub::createFromPath(__DIR__.'/../Stubs/migration/delete.stub', [
                'class'       => $this->getClass(),
                'table'       => $this->table->getName(),
                'fields_down' => $this->constructSchema(),
                'fields_up'   => $this->constructSchema('drop'),
            ]);
        } elseif ($this->action == 'drop') {
            return Stub::createFromPath(__DIR__.'/../Stubs/migration/drop.stub', [
                'class'  => $this->getClass(),
                'table'  => $this->table->getName(),
                'fields' => $this->constructSchema(),
            ]);
        }

        return parent::getStub();
    }

    /**
     * Construct the schema fields.
     *
     * @param  string $direction
     * @return array
     */
    private function constructSchema($direction = 'add')
    {
        $fields = array_map(function ($field) use ($direction) {
            $method = "{$direction}Column";
            return $this->$method($field);
        }, $this->table->getFields());

        if ($direction == 'add' && !$this->onlyForeign) {
            $fields = array_merge($fields, [$this->createPrimaryKeys()]);
        }

        // clean empty fields
        $fields = array_filter($fields, function($field) {
            return !empty($field);
        });

        return implode("\n" . str_repeat(' ', 12), $fields);
    }

    /**
     * Construct the syntax to add a column.
     *
     * @param  Field $field
     * @return string
     */
    private function addColumn(Field $field)
    {
        $syntax = '';

        if (!$this->onlyForeign) {
            $syntax = sprintf("\$table->%s('%s')", $field->getType(), $field->getName());

            // If there are arguments for the schema type, like decimal('amount', 5, 2)
            // then we have to remember to work those in.
            if ($field->hasArguments()) {
                $syntax = substr($syntax, 0, -1) . ', ';
                $syntax .= implode(', ', $field->getArguments()) . ')';
            }

            if ($field->isUnique())   $syntax .= "->unique()";
            if ($field->isNullable()) $syntax .= "->nullable()";
            if ($field->isUnsigned()) $syntax .= "->unsigned()";
            if ($field->hasDefault()) $syntax .= "->default({$field->getDefault()})";

            $syntax .= ';';

            if ($field->isIndex()) {
                $syntax .= "\n" . str_repeat(' ', 12) . "\$table->index('{$field->getName()}');";
            }
        }

        if ($field->hasForeign() && ($this->generateForeign || $this->onlyForeign)) {
            $foreign = $field->getForeign();
            $syntax .= "\n" . str_repeat(' ', 12)
                    . sprintf("\$table->foreign('%s')->references('%s')->on('%s');", $this->getForeignKeyName($field),
                              $foreign->getReferences(), $foreign->getOn());
        }

        return $syntax;
    }

    /**
     * Construct the syntax to drop a column.
     *
     * @param  Field $field
     * @return string
     */
    private function dropColumn(Field $field)
    {
        $syntax = '';

        if (($field->hasForeign() && $this->generateForeign) || $this->onlyForeign) {
            $syntax .= sprintf("\$table->dropForeign('%s');", $this->getForeignKeyName($field, true))
                    . "\n" . str_repeat(' ', 12);
        }

        if (!$this->onlyForeign) {
            $syntax .= sprintf("\$table->dropColumn('%s');", $field->getName());
        }

        return $syntax;
    }

    /**
     * Generate the primary key(s) for the table.
     *
     * @return array|string
     */
    private function createPrimaryKeys()
    {
        $pks = [];

        foreach ($this->table->getFields() as $field) {
            if ($field->isPrimary()) {
                $pks[] = "'".$field->getName()."'";
            }
        }

        if (sizeof($pks) == 1) {
            return sprintf("\$table->primary(%s);", $pks[0]);
        } elseif (sizeof($pks) > 1) {
            return sprintf("\$table->primary([%s]);", implode(',', $pks));
        } else {
            return '';
        }
    }

    /**
     * @param Field $field
     * @param bool $isDrop
     * @return string
     */
    private function getForeignKeyName(Field $field, $isDrop = false)
    {
        $foreign = $field->getForeign();
        $name    = $field->getName();

        if (!empty($foreign->getName())) {
            $name = $foreign->getName();
        }

        if ($isDrop && empty($foreign->getName())) {
            $name = str_replace(array('-', '.'), '_', sprintf("%s_%s_foreign", $this->table->getName(), $field->getName()));
        }

        return $name;
    }
}
