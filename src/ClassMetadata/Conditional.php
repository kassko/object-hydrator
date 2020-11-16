<?php

namespace Big\Hydrator\ClassMetadata;

use Big\StandardClassMetadata\Method;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Conditional extends Base
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var string
     */
    public string $id;
    //=== Annotations attributes : end ===//

    public function getId() : string
    {
        return $this->id;
    }
}
