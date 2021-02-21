<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author kko
 */
final class NotToAutoconfigure
{
    use Capability\Enabling;
}
