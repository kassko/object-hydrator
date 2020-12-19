<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class _001_PerformBasicObjectHydrationTest extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $rawData = [
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
        ];

        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
            private ?string $lastName = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());//Ensure id passed to constructor is not loose after object hydration.
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
    }
}
