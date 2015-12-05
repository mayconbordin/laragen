<?php namespace Mayconbordin\Laragen\Generator;

use Mayconbordin\Laragen\FormDumpers\FieldsDumper;
use Mayconbordin\Laragen\FormDumpers\TableDumper;

class FormGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'form';

    /**
     * @var string
     */
    protected $fields;

    /**
     * FormGenerator constructor.
     *
     * @param array $options [ fields=The form fields ]
     */
    public function __construct(array $options = [])
    {
        parent::__construct('view', $options);

        $this->fields = array_get($options, 'fields', null);
    }

    /**
     * Render the form.
     *
     * @return string
     */
    public function render()
    {
        if ($this->fields) {
            return $this->renderFromFields();
        }

        return $this->renderFromDb();
    }

    /**
     * Render form from database.
     *
     * @return string
     */
    public function renderFromDb()
    {
        return (new TableDumper($this->name))->render();
    }

    /**
     * Render form from fields option.
     *
     * @return string
     */
    public function renderFromFields()
    {
        return (new FieldsDumper($this->fields))->render();
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getFileName()
    {
        return strtolower($this->getName()).'.blade';
    }
    
    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'fields' => $this->render(),
        ]);
    }
}
