<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use Kassko\ObjectHydratorTest\Helper\ReflectionTrait;
use PHPUnit\Framework\TestCase;

class _022_HydratePropertyObject_DataSource_LazyLoading_Test extends TestCase
{
    use ReflectionTrait;

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
            use LoadableTrait;

            private int $id;
            private ?string $firstName = null;
            private ?string $lastName = null;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      dataSource=@BHY\DataSource(
             *          method=@BHY\Method(
             *              class="Kassko\ObjectHydratorTest\Integration\Fixture\Service\EmailService",
             *              name="getData",
             *              args={"#id"}
             *          ),
             *          indexedByPropertiesKeys=false
             *      )
             *  )
             */
            private ?string $email = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getEmail() : ?string { $this->loadProperty('email'); return $this->email; }
            public function setEmail(string $email) { var_dump('ICI'); $this->email = $email; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());//Ensure id passed to constructor is not loose after object hydration.
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());

        //Check if lazy loading was not triggered and so if property is not fed.
        $this->assertNull($this->getPropertyValue($person, 'email'));

        //Check if lazy loading was triggered and so if property is now fed.
        $this->assertSame('dany@gomes', $person->getEmail());
    }
}
