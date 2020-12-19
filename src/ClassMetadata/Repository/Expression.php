<?php

namespace Big\Hydrator\ClassMetaData\Repository;

use Big\Hydrator\ClassMetadata\Model;

/**
 * @author kko
 */
class Expression
{
    private array $expressions = [];

    public function find(string $id) : ?Model\Expression
    {
        return isset($this->expressions[$id]) ? $this->expressions[$id] : null;
    }

    public function add(Model\Expression $expression) : self
    {
        $this->expressions[$expression->getId()] = $expression;

        return $this;
    }

    public function addCollection(array $expressions) : self
    {
        foreach ($expressions as $expression) {
            $this->add($expression);
        }

        return $this;
    }
}
