<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _026_HydratePropertyObject_DataSource_DoNotHydrateProvidedButNotAnnotatedProperties_Test extends TestCase
{
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
        $person = new class(1) {
            use LoadableTrait;

            private int $id;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      dataSource=@BHY\DataSource(
             *          method=@BHY\Method(
             *              class="Kassko\ObjectHydratorIntegrationTest\Fixture\Service\PersonService",
             *              name="getData",
             *              args={"#id"}
             *          )
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


        $this->hydrator->hydrate($person);

        $this->assertSame(1, $person->getId());
        $this->assertEquals('Dany', $person->getFirstName());
        $this->assertNull($person->getLastName());
        $this->assertNull($person->getPhone());
    }
}
