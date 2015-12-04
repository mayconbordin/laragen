<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Helpers\FieldValidationHelper;
use Mayconbordin\Generator\Parsers\SchemaParser;

class ModelGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'model';

    /**
     * @var string
     */
    protected $fillable;

    /**
     * @var string
     */
    protected $fields;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * ModelGenerator constructor.
     *
     * @param array $options [ fillable=List of fillable fields, comma separated;
     *                         fields=List of fields (with its descriptions), comma separated;
     *                         table_name=The name of the table, if different than the model name ]
     */
    public function __construct(array $options = array())
    {
        parent::__construct('model', $options);

        $this->fillable  = array_get($options, 'fillable', null);
        $this->fields    = array_get($options, 'fields', null);
        $this->tableName = array_get($options, 'table_name', null);
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'fillable'   => $this->getFillable(),
            'table_name' => $this->getTableName(),
            'relations'  => '',
            'rules'      => $this->getFieldRules()
        ]);
    }

    /**
     * Get the fillable attributes.
     *
     * @return string
     */
    public function getFillable()
    {
        if (!$this->fillable) {
            return '[]';
        }

        $results = '['.PHP_EOL;

        foreach ((new SchemaParser())->parse($this->fillable) as $field) {
            $results .= "\t\t'{$field->getName()}',".PHP_EOL;
        }

        return $results."\t".']';
    }

    /**
     * Get the name of the table, if informed.
     *
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->tableName)) {
            return '';
        }

        return "protected \$table = '{$this->tableName}';";
    }

    /**
     * @return string
     */
    public function getFieldRules()
    {
        $results = '';

        foreach ((new SchemaParser())->parse($this->fields) as $field) {
            $rules = FieldValidationHelper::toRules($field, $this->tableName);
            $results .= str_repeat(' ', 8) . "'{$field->getName()}' => '".implode('|', $rules)."',".PHP_EOL;
        }

        return $results;
    }
}
