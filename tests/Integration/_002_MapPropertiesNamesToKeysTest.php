<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydrator\PrettyAnonymousClassNameProviderInterface;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _002_MapPropertiesNamesToKeysTest extends TestCase
{
    use Helper\IntegrationTestTrait;

    /*public function setup() : void
    {
        $this->initHydrator([[
            'class_metadata' => [
                'Person' => [
                    'method_ressource' => [
                        'type' => 'php',
                        'method_name' => 'getClassMetadata'
                    ]
                ]
            ]
        ]]);
    }
*/
    private function doctrineAnnotationConfigProvider()
    {
        return [];
    }

    private function doctrineInnerPhpConfigProvider()
    {
        return [[
            'class_metadata' => [
                'Person' => [
                    'method_ressource' => [
                        'type' => 'php',
                        'method_name' => 'getClassMetadataAsPhpArray'
                    ]
                ]
            ]
        ]];
    }

    private function doctrineInnerYamlConfigProvider()
    {
        return [[
            'class_metadata' => [
                'Person' => [
                    'method_ressource' => [
                        'type' => 'yaml',
                        'method_name' => 'getClassMetadataAsYaml'
                    ]
                ]
            ]
        ]];
    }

    public function configProvider()
    {
        return [
            [$this->doctrineAnnotationConfigProvider()],
            [$this->doctrineInnerPhpConfigProvider()],
            [$this->doctrineInnerYamlConfigProvider()],
        ];
    }

    /**
     * @test
     * @dataProvider configProvider
     */
    public function letsGo(array $config)
    {
        $this->initHydrator($config);

        $rawData = [
            'firstName' => 'Dany',
            'lastName' => 'Gomes',
            'email' => 'Dany@Gomes',
        ];

        $person = new class(1) {

            private int $id;
            /**
             * @BHY\PropertyConfig\SingleType(keyInRawData="firstName")
             */
            private ?string $firstName = null;
            /**
             * @BHY\PropertyConfig\SingleType(keyInRawData="lastName")
             */
            private ?string $lastName = null;
            private ?string $email = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getEmail() : ?string { return $this->email; }
            public function setEmail(string $email) { $this->email = $email; }

            public static function providePrettyClassName() : string {
                return 'Person';
            }

            public function getClassMetadataAsPhpArray() {
                return [
                    'properties' => [
                        'firstName' => [
                            'single_type' => [
                                'key_in_raw_data' => 'firstName',
                            ],
                        ],
                        'lastName' => [
                            'single_type' => [
                                'key_in_raw_data' => 'lastName',
                            ],
                        ],
                    ]
                ];
            }

            public function getClassMetadataAsYaml() {
                return <<<EOF
properties:
    firstName:
        single_type:
            key_in_raw_data: firstName
    lastName:
        single_type:
            key_in_raw_data: lastName
EOF;
            }//function
        };//new class()


        $this->hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }
}
