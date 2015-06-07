<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\FormDumpers\FieldsDumper;
use Mayconbordin\Generator\FormDumpers\TableDumper;

class FormGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'form';

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
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return base_path().'/resources/views/';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath().strtolower($this->getName()).'.blade.php';
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
