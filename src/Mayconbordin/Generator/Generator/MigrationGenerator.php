<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Migrations\NameParser;
use Mayconbordin\Generator\Migrations\SchemaParser;
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
     * MigrationGenerator constructor.
     */
    public function __construct(array $options = array())
    {
        parent::__construct('migration', $options);
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
            $syntax .= "\n" . str_repeat(' ', 12) . "\$table->index('{$field->getName()}')";
        }

        if ($field->hasForeign()) {
            $foreign = $field->getForeign();
            $syntax .= "\n" . str_repeat(' ', 12)
                    . sprintf("\$table->foreign('%s')->references('%s')->on('%s');", $field->getName(), $foreign['references'], $foreign['on']);
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
        $syntax = "";

        if ($field->hasForeign()) {
            $syntax .= sprintf("\$table->dropForeign('%s_%s_foreign');", $this->table->getName(), $field->getName())
                    . "\n" . str_repeat(' ', 12);
        }

        $syntax .= sprintf("\$table->dropColumn('%s');", $field->getName());

        return $syntax;
    }
}
