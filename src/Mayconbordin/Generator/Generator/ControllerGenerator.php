<?php namespace Mayconbordin\Generator\Generator;

use Mayconbordin\Generator\Scaffolders\ControllerScaffolder;

class ControllerGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'controller/plain';

    /**
     * @var bool
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $scaffold;

    /**
     * @var bool
     */
    protected $repository;

    /**
     * ControllerGenerator constructor.
     *
     * @param array $options [ resource=If the controller should use the resource template;
     *                         scaffold=If the controller should use the scaffold template;
     *                         repository=If the controller should use the repository scaffold template ]
     */
    public function __construct(array $options = [])
    {
        parent::__construct('controller', $options);

        $this->resource   = array_get($options, 'resource', false);
        $this->scaffold   = array_get($options, 'scaffold', false);
        $this->repository = array_get($options, 'repository', false);
    }

    /**
     * Configure some data.
     */
    public function setUp()
    {
        if ($this->resource) {
            $this->stub = 'controller/resource';
        } elseif ($this->scaffold) {
            $this->stub = 'controller/scaffold';
            $this->scaffolder = new ControllerScaffolder($this->getClass(), $this->getPrefix());
        } elseif ($this->repository) {
            $this->stub = 'controller/scaffold_repository';
            $this->scaffolder = new ControllerScaffolder($this->getClass(), $this->getPrefix());
        }
    }

    /**
     * Get prefix class.
     *
     * @return string
     */
    public function getPrefix()
    {
        $paths = explode('/', $this->getName());

        array_pop($paths);

        return strtolower(implode('\\', $paths));
    }

    /**
     * Get template replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        $replacements = parent::getReplacements();

        if ($this->scaffold || $this->repository) {
            return array_merge($replacements, $this->scaffolder->toArray());
        }

        return $replacements;
    }
}
