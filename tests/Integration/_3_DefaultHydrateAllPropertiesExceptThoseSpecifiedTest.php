<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{ClassMetadata as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class _3_DefaultHydrateAllPropertiesExceptThoseSpecifiedTest extends TestCase
{
    /**
     * @test
     */
    public function defaultHydrateAllPropertiesExceptThoseSpecified()
    {
        $rawData = [
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
            'email' => 'Dany@Gomes',
        ];

        $person = new class(1) {
            private int $id;
            /**
             * @BHY\Property(keyInRawData="first_name")
             */
            private ?string $firstName = null;
            /**
             * @BHY\Property(keyInRawData="last_name")
             */
            private ?string $lastName = null;
            /**
             * @BHY\ExcludedProperty
             */
            private ?string $email = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getEmail() : ?string { return $this->email; }
            public function setEmail(string $email) { $this->email = $email; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertNull($person->getEmail());
    }
}
