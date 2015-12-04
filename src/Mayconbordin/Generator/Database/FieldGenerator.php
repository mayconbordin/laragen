<?php namespace Mayconbordin\Generator\Database;

use \DB;
use Illuminate\Support\Str;
use Mayconbordin\Generator\Schema\Field;

class FieldGenerator
{
    /**
     * Convert dbal types to Laravel Migration Types
     * @var array
     */
    protected $fieldTypeMap = [
        'tinyint'  => 'tinyInteger',
        'smallint' => 'smallInteger',
        'bigint'   => 'bigInteger',
        'datetime' => 'dateTime',
        'blob'     => 'binary',
    ];

    /**
     * @var string
     */
    protected $database;

    /**
     * Create array of all the fields for a table
     *
     * @param string                                      $table Table Name
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     * @param string                                      $database
     * @param bool                                        $ignoreIndexNames
     *
     * @return array
     */
    public function generate($table, $schema, $database, $ignoreIndexNames)
    {
        $this->database = $database;
        $columns = $schema->listTableColumns($table);

        if (empty($columns)) return [];

        $indexGenerator = new IndexGenerator($table, $schema, $ignoreIndexNames);
        $fields = $this->setEnum($this->getFields($columns, $indexGenerator), $table);
        $fields = $this->getMultiFieldIndexes($fields, $indexGenerator);

        return $fields;
    }

    /**
     * Return all enum columns for a given table
     * @param string $table
     * @return array
     */
    protected function getEnum($table)
    {
        try {
            $result = DB::table('information_schema.columns')
                ->where('table_schema', $this->database)
                ->where('table_name', $table)
                ->where('data_type', 'enum')
                ->get(['column_name','column_type']);

            return ($result) ? $result : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param array $fields
     * @param string $table
     * @return array
     */
    protected function setEnum(array $fields, $table)
    {
        foreach ($this->getEnum($table) as $column) {
            $field = $fields[$column->column_name];
            $field->setType('enum');
            $field->setArguments(explode(',', str_replace(['enum(', ')'], '', $column->column_type)));
        }

        return $fields;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column[] $columns
     * @param IndexGenerator $indexGenerator
     * @return array
     */
    protected function getFields($columns, IndexGenerator $indexGenerator)
    {
        $fields = array();

        foreach ($columns as $column) {
            $name     = $column->getName();
            $type     = $column->getType()->getName();
            $length   = $column->getLength();
            $default  = $column->getDefault();
            $nullable = (!$column->getNotNull());
            $index    = $indexGenerator->getIndex($name);
            $unsigned = $column->getUnsigned();

            $decorators = null;
            $args     = null;

            if (isset($this->fieldTypeMap[$type])) {
                $type = $this->fieldTypeMap[$type];
            }

            // Different rules for different type groups
            if (in_array($type, ['tinyInteger', 'smallInteger', 'integer', 'bigInteger'])) {
                // Integer
                if ($type == 'integer' && /*$column->getUnsigned() &&*/ $column->getAutoincrement()) {
                    $type = 'increments';
                    $index = null;
                } elseif ($type == 'bigInteger' && /*$column->getUnsigned() &&*/ $column->getAutoincrement()) {
                    $type = 'bigIncrements';
                    $index = null;
                } else {
                    if ($column->getAutoincrement()) {
                        $index = null;
                    }
                }
            } elseif (in_array($type, ['dateTime', 'date', 'time', 'timestamp'])) {
                // do nothing for now
            } elseif (in_array($type, ['decimal', 'float', 'double'])) {
                // Precision based numbers
                $args = $this->getPrecision($column->getPrecision(), $column->getScale());
            } else {
                // Probably not a number (string/char)
                if ($type === 'string' && $column->getFixed()) {
                    $type = 'char';
                }

                $args = $this->getLength($length);
            }


            $field = [
                'name'      => $name,
                'type'      => $type,
                'arguments' => $args,
                'options'   => [
                    'nullable' => $nullable,
                    'default'  => $default,
                    'unique'   => ($index && $index->type == 'unique'),
                    'index'    => ($index && !Str::startsWith($index->name, 'fk') && $index->type != 'unique'),
                    'unsigned' => $unsigned
                ]
            ];

            $fields[$name] = new Field($field);
        }

        return $fields;
    }

    /**
     * @param int $length
     * @return int|void
     */
    protected function getLength($length)
    {
        if ($length) {
            return [$length];
        }
    }

    /**
     * @param string $default
     * @param string $type
     * @return string
     */
    protected function getDefault($default, &$type)
    {
        if (in_array($default, ['CURRENT_TIMESTAMP'])) {
            if ($type == 'dateTime')
                $type = 'timestamp';

            $default = $this->decorate('DB::raw', $default);
        } elseif (in_array($type, ['string', 'text']) or !is_numeric($default)) {
            $default = $this->argsToString($default);
        }

        return $this->decorate('default', $default, '');
    }

    /**
     * @param int $precision
     * @param int $scale
     * @return array|void
     */
    protected function getPrecision($precision, $scale)
    {
        //if ($precision != 8 or $scale != 2) {
        $result = [$precision];

        if ($scale != 2) {
            $result[] = $scale;
        }

        return $result;
        //}
    }

    /**
     * @param string|array $args
     * @param string       $quotes
     * @return string
     */
    protected function argsToString($args, $quotes = '\'')
    {
        if (is_array($args)) {
            $seperator = $quotes .', '. $quotes;
            $args = implode($seperator, $args);
        }

        return $quotes . $args . $quotes;
    }

    /**
     * Get Decorator
     * @param string       $function
     * @param string|array $args
     * @param string       $quotes
     * @return string
     */
    protected function decorate($function, $args, $quotes = '\'')
    {
        if (!is_null($args)) {
            $args = $this->argsToString($args, $quotes);
            return $function . '(' . $args . ')';
        } else {
            return $function;
        }
    }

    /**
     * @param array $fields
     * @param IndexGenerator $indexGenerator
     * @return array
     */
    protected function getMultiFieldIndexes(array $fields, IndexGenerator $indexGenerator)
    {
        foreach ($indexGenerator->getMultiFieldIndexes() as $index) {
            if ($index->type == 'primary') {
                foreach ($index->columns as $column) {
                    if (isset($fields[$column])) {
                        $fields[$column]->setPrimary(true);
                    }
                }
            }
        }

        return $fields;
    }
}