<?php namespace Mayconbordin\Generator\Generator;

class SeedGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'seed';

    /**
     * @var bool
     */
    protected $master;

    /**
     * SeedGenerator constructor.
     *
     * @param array $options [ master=Create a master seed ]
     */
    public function __construct(array $options = array())
    {
        parent::__construct('seed', $options);

        $this->master = array_get($options, 'master', false);
    }

    /**
     * Get name of class.
     *
     * @return string
     */
    public function getName()
    {
        $name = parent::getName();

        if ($this->master) {
            return $name.'DatabaseSeeder';
        }

        return $name.'TableSeeder';
    }
}
