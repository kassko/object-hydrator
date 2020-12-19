<?php

namespace Big\Hydrator\ClassMetadata\Model;

/**
 * @author kko
 */
abstract class Property
{
    private string $name;
    private array $variables = [];


    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function isCollection()
    {
        return false;
    }

    public function hasCandidates()
    {
        return false;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function hasVariables() : bool
    {
        return count($this->variables) > 0;
    }

    public function getVariables() : array
    {
        return $this->variables;
    }

    public function setVariables(array $variables) : self
    {
        $this->variables = $variables;

        return $this;
    }
}
