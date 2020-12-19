<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use PHPUnit\Framework\TestCase;

class _017_HydratePropertyObject_DataSource_DoNotHydrateProvidePropertiesNotAnnotated_Test extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $rawData = [
        ];

        $person = new class(1) {
            use LoadableTrait;

            private int $id;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      dataSource=@BHY\DataSource(
             *          method=@BHY\Method(
             *              class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
             *              name="getData",
             *              args={"#id"}
             *          ),
             *          indexedByPropertiesKeys=true
             *      )
             *  )
             */
            private ?string $firstName = null;
            private ?string $lastName = null;
            private ?string $phone = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { $this->loadProperty('firstName'); return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getPhone() : ?string { return $this->phone; }
            public function setPhone(string $phone) { $this->phone = $phone; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertEquals('Dany', $person->getFirstName());
        $this->assertNull($person->getLastName());
        $this->assertNull($person->getPhone());
    }
}
