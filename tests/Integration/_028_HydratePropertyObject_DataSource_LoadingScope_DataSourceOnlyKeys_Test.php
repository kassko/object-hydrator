<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _028_HydratePropertyObject_DataSource_LoadingScope_DataSourceOnlyKeys_Test extends TestCase
{
    use Helper\ReflectionTrait;
    use Helper\IntegrationTestTrait;

    public function setup() : void
    {
        $this->initHydrator();
    }

    /**
     * @test
     */
    public function letsGo()
    {
        /**
         * @BHY\DataSource(
         *      id="person",
         *      method=@BHY\Method(
         *          class="Kassko\ObjectHydratorIntegrationTest\Fixture\Service\PersonService",
         *          name="getData",
         *          args={"#id"}
         *      ),
         *      loadingScope="data_source_only_keys",
         *      loadingScopeKeys={"first_name", "last_name"}
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

            public function getLastName() : ?string { $this->loadProperty('lastName'); return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getPhone() : ?string { $this->loadProperty('phone'); return $this->phone; }
            public function setPhone(string $phone) { $this->phone = $phone; }
        };


        $this->hydrator->hydrate($person);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        //Check if property is well not loaded without using the getter to avoid triggering a load.
        $this->assertNull($this->getPropertyValue($person, 'phone'));
    }
}
