<?php namespace Mayconbordin\Laragen\Generator;

class ViewGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'view';

    /**
     * @var string
     */
    protected $extends;

    /**
     * @var string
     */
    protected $section;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var bool
     */
    protected $master;

    /**
     * @var bool
     */
    protected $plain;

    /**
     * The array of custom replacements.
     *
     * @var array
     */
    protected $customReplacements = [];

    /**
     * ViewGenerator constructor.
     *
     * @param array $options [ extends=The name of view layout being used;
     *                         section=The name of section being used;
     *                         content=The view content;
     *                         template=The path of view template;
     *                         master=Create a master view;
     *                         plain=Create a blank view ]
     */
    public function __construct(array $options = array())
    {
        parent::__construct('view', $options);

        $this->extends  = array_get($options, 'extends', 'layouts.master');
        $this->section  = array_get($options, 'section', 'content');
        $this->content  = array_get($options, 'content', null);
        $this->template = array_get($options, 'template', null);
        $this->master   = array_get($options, 'master', false);
        $this->plain    = array_get($options, 'plain', false);
    }

    /**
     * Setup.
     */
    public function setUp()
    {
        if ($this->master) {
            $this->stub = 'views/master';
        }
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
     * Get stub template for generated file.
     *
     * @return string
     */
    public function getStub()
    {
        if ($this->plain) {
            return $this->getPath();
        }

        if ($template = $this->template) {
            return Stub::create($template, $this->getReplacements())->render();
        }

        return parent::getStub();
    }

    /**
     * Get template replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'extends' => $this->extends,
            'section' => $this->section,
            'content' => $this->content,
        ], $this->customReplacements);
    }

    /**
     * Append a custom replacements to this instance.
     *
     * @param array $replacements
     *
     * @return self
     */
    public function appendReplacement(array $replacements)
    {
        $this->customReplacements = $replacements;
        return $this;
    }
}
