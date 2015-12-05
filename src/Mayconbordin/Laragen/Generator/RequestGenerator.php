<?php namespace Mayconbordin\Laragen\Generator;

use Mayconbordin\Laragen\Helpers\FieldValidationHelper;
use Mayconbordin\Laragen\Parsers\SchemaParser;

class RequestGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'request';

    /**
     * @var bool
     */
    protected $auth;

    /**
     * @var string
     */
    protected $rules;

    /**
     * @var string
     */
    protected $fields;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * RequestGenerator constructor.
     *
     * @param array $options [ auth=If the user is authorized to make the request (Default: true);
     *                         rules=List of comma-separated fields and rules;
     *                         fields=List of fields (with its descriptions), comma separated;
     *                         table_name=The name of the table, if different than the model name ]
     */
    public function __construct(array $options = [])
    {
        parent::__construct('request', $options);

        $this->auth      = array_get($options, 'auth', false);
        $this->rules     = array_get($options, 'rules', null);
        $this->fields    = array_get($options, 'fields', null);
        $this->tableName = array_get($options, 'table_name', null);
    }

    /**
     * Get stub replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'auth'  => $this->getAuth(),
            'rules' => $this->getRules(),
        ]);
    }

    /**
     * Get auth replacement.
     *
     * @return string
     */
    public function getAuth()
    {
        $authorize = $this->auth ? 'true' : 'false';

        return 'return '.$authorize.';';
    }

    /**
     * Get replacement for "$RULES$".
     *
     * @return string
     */
    public function getRules()
    {
        if (empty($this->rules) && empty($this->fields)) {
            return 'return [];';
        }

        $results = 'return ['.PHP_EOL;

        if (!empty($this->rules)) {
            foreach ((new SchemaParser())->parseRules($this->rules) as $field => $rules) {
                $results .= $this->createRules($field, $rules);
            }
        } else {
            foreach ((new SchemaParser())->parse($this->fields) as $field) {
                $rules = FieldValidationHelper::toRules($field, $this->tableName);
                $results .= $this->createRules($field->getName(), $rules);
            }
        }

        $results .= "\t\t];";

        return $results;
    }

    /**
     * Create a rule.
     *
     * @param string $field
     * @param string $rules
     *
     * @return string
     */
    protected function createRules($field, $rules)
    {
        $rule = implode('|', $rules);
        return "\t\t\t'{$field}' => '".$rule."',".PHP_EOL;
    }
}
