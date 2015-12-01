<?php namespace Mayconbordin\Generator\Exceptions;

class InvalidFieldTypeException extends GeneratorException
{
    private $type;

    public function __construct($type)
    {
        parent::__construct("The '$type' type is not valid.");

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
