<?php

namespace Big\Hydrator\Annotation\Doctrine;

use Big\Hydrator\ClassMetadata\Model;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 *
 * @author kko
 */
final class DataSource
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public Method $method;
    /**
     * @internal
     * @var string
     */
    public string $loadingMode = Model\DataSource::LOADING_MODE_LAZY;
    /**
     * @internal
     * @var bool
     */
    public bool $indexedByPropertiesKeys = false;
    /**
     * @internal
     * @var string
     */
    public string $loadingScope = Model\DataSource::LOADING_SCOPE_DATA_SOURCE;
    /**
     * @internal
     * @var array
     */
    public array $loadingScopeKeys = [];
    /**
     * @internal
     */
    public ?string $fallbackDataSourceRef = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksUsingMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksFetchingData = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksAssigningHydratedValue = null;
}
