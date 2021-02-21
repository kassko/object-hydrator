<?php

namespace Kassko\ObjectHydratorUnitTest\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadataLoader\DoctrineAnnotationLoader;
use PHPUnit\Framework\TestCase;

class DoctrineAnnotationLoaderTest extends TestCase
{
    public function arrayProvider()
    {
        return [
            [
                ['collection' => ['foo', 'bar']],
                ['collection' => ['items' => ['foo', 'bar']]],
                'collection',
                'items'
            ],
            [
                [
                    'collection' => [
                        'foo_key' => 'foo',
                        'collection' => [
                            'bar_key' => 'bar',
                            'baz_key' => 'baz'
                        ]
                    ]
                ],
                [
                    'collection' => [
                        'items' => [
                            'foo_key' => 'foo',
                            'collection' => [
                                'items' => ['bar_key' => 'bar'],
                                'baz_key' => 'baz'
                            ]
                        ]
                    ]
                ],
                'collection',
                'items'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider arrayProvider
     */
    public function removeArrayDimension($expected, $actual, $keyToWorkOn, $childKeyToRemove)
    {
        DoctrineAnnotationLoader::removeArrayDimension($actual, [$keyToWorkOn => $childKeyToRemove]);

        $this->assertEquals($expected, $actual);
    }
}
