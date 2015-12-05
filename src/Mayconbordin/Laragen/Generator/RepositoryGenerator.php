<?php namespace Mayconbordin\Laragen\Generator;

use Mayconbordin\Laragen\Scaffolders\ParameterScaffolder;

class RepositoryGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'repository';

    /**
     * @var string
     */
    protected $entity;

    /**
     * ModelGenerator constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct('repository', $options);

        $this->entity = array_get($options, 'entity', null);
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), (new ParameterScaffolder($this->getName(), null, 'repository', $this->entity))->toArray());
    }
}
