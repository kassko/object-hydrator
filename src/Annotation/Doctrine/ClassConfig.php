<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

use Kassko\ObjectHydrator\ClassMetadata\Model\Enum;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
final class ClassConfig
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * @var bool
     */
    public $defaultAutoconfigureProperties = true;
    /**
     * @var string
     */
    public $rawDataKeyStyle = Enum\RawDataKeyStyle::UNDERSCORE;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public ?Method $rawDataKeyStyleConverter = null;
    /**
     * @var string
     */
    public ?string $defaultAdderNameFormat = null;
    /**
     * @var bool
     */
    public bool $accessorsToBypass = false;
}
