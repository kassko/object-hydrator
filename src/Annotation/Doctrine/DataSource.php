<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

use Kassko\ObjectHydrator\Model\Enum;

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
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public Method $method;
    /**
     * @internal
     * @var string
     */
    public string $loadingMode = Enum\DataSourceLoadingMode::LAZY;
    /**
     * @internal
     * @var bool
     */
    public bool $indexedByPropertiesKeys = true;
    /**
     * @internal
     * @var string
     */
    public string $loadingScope = Enum\DataSourceLoadingScope::DATA_SOURCE;
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
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksUsingMetadata = null;
    /**
     * @internal
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Callbacks
     */
    public ?Callbacks $callbacksFetchingData = null;
}
