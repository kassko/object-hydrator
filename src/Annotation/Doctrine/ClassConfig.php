<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
    public $rawDataKeyStyle = RawDataKeyStyleEnum::RAW_DATA_KEY_STYLE_UNDERSCORE;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public ?Method $toRawDataKeyStyleConverter = null;
    /**
     * @var string
     */
    public ?string $defaultAdderNameFormat = null;
}
