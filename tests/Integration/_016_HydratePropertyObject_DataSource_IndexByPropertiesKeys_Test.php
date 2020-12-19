<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use PHPUnit\Framework\TestCase;

class _016_HydratePropertyObject_DataSource_IndexByPropertiesKeys_Test extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $rawData = [
        ];

        /**
         * @BHY\DataSource(
         *      id="person",
         *      method=@BHY\Method(
         *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
         *          name="getData",
         *          args={"#id"}
         *      ),
         *      indexedByPropertiesKeys=true
         * )
         */
        $person = new class(1) {
            use LoadableTrait;

            private int $id;
            /**
             * @BHY\PropertyConfig\SingleType(dataSourceRef="person")
             */
            private ?string $firstName = null;
            /**
             * @BHY\PropertyConfig\SingleType(dataSourceRef="person")
             */
            private ?string $lastName = null;
            /**
             * @BHY\PropertyConfig\SingleType(dataSourceRef="person")
             */
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
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('01 02 03 04 05', $person->getPhone());
    }
}
