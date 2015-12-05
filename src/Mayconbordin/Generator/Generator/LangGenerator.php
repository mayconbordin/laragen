<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Exceptions\InvalidFormatException;
use Mayconbordin\Generator\Helpers\FieldValidationHelper;
use Mayconbordin\Generator\Parsers\SchemaParser;

class LangGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'lang';

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $translations;

    /**
     * ModelGenerator constructor.
     *
     * @param array $options [ fillable=List of fillable fields, comma separated;
     *                         fields=List of fields (with its descriptions), comma separated;
     *                         table_name=The name of the table, if different than the model name ]
     */
    public function __construct(array $options = array())
    {
        parent::__construct('lang', $options);

        $this->language     = array_get($options, 'language', 'en');
        $this->translations = array_get($options, 'translations', null);
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->language.'/'.strtolower($this->getName());
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'translations' => $this->getTranslations()
        ]);
    }

    /**
     * Get the fillable attributes.
     *
     * @return string
     * @throws InvalidFormatException
     */
    public function getTranslations()
    {
        if (!$this->translations) {
            return '';
        }

        // match key='value' pairs
        $matches = preg_match_all('/(\w+)\s*=\s*(["\'])((?:(?!\2).)*)\2/', $this->translations, $translations, PREG_SET_ORDER);

        if (strlen($this->translations) > 0 && $matches == 0) {
            throw new InvalidFormatException("Translations should follow the format: <key1>='<value1>', <key2>='<value2>'.");
        }

        $results = '';

        foreach ($translations as $translation) {
            if (sizeof($translation) != 4) continue; //should throw an exception here
            $results .= str_repeat(" ", 4) . "'{$translation[1]}' => '{$translation[3]}'," . PHP_EOL;
        }

        return $results;
    }
}
