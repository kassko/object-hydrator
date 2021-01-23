<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model\Enum;

final class DataSourceLoadingScope
{
    public const DATA_SOURCE = 'data_source';
    public const PROPERTY = 'property';
    public const DATA_SOURCE_ONLY_KEYS = 'data_source_only_keys';
    public const DATA_SOURCE_EXCEPT_KEYS = 'data_source_except_keys';
}
