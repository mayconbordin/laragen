<?php namespace Mayconbordin\Generator\Schema;


class Foreign
{
    /**
     * @var string The name of the foreign key.
     */
    protected $name;

    /**
     * @var string The name of the field being referenced on the foreign table.
     */
    protected $references;

    /**
     * @var string The name of the foreign table being referenced.
     */
    protected $on;

    /**
     * Foreign constructor.
     * @param string $name
     * @param string $references
     * @param string $on
     */
    public function __construct($on, $references = 'id', $name = null)
    {
        $this->name = $name;
        $this->references = $references;
        $this->on = $on;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param string $references
     */
    public function setReferences($references)
    {
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getOn()
    {
        return $this->on;
    }

    /**
     * @param string $on
     */
    public function setOn($on)
    {
        $this->on = $on;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return 'Foreign {name='.(is_null($this->name) ? 'null' : $this->name).', references='.$this->references.', on='.$this->on.'}';
    }


}