<?php namespace Mayconbordin\Generator\Exceptions;


class MethodNotFoundException extends GeneratorException
{
    private $method;

    public function __construct($method)
    {
        parent::__construct("The '$method' type is not valid.");

        $this->method = $method;
    }
}