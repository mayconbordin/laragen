<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Scaffolders\ControllerScaffolder;

class RepositoryGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'repository';

    /**
     * ModelGenerator constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct('repository', $options);
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), (new ControllerScaffolder($this->getName()))->toArray());
    }
}
