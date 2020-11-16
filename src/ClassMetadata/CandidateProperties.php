<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target({"CLASS","PROPERTY"})
 *
 * @author kko
 */
class CandidateProperties extends Base
{
	//=== Annotations attributes (must be public) : begin ===//
    /**
     * One or more DataSource annotations.
     *
     * @var array<\Big\Hydrator\ClassMetadata\Property>
     */
    public $items = [];
    /**
     * @internal
     * @var string
     */
    private string $name;
    /**
     * @internal
     * @var array
     */
    public array $variables = [];
    //=== Annotations attributes : end ===//

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
}
