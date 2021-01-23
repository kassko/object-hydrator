<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use PHPUnit\Framework\TestCase;

class _023_HydratePropertyObject_DataSource_EagerLoading_Test extends TestCase
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
             *          indexedByPropertiesKeys=false,
             *          loadingMode="eager"
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

            public function getEmail() : ?string { return $this->email; }
            public function setEmail(string $email) { $this->email = $email; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());//Ensure id passed to constructor is not loose after object hydration.
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('dany@gomes', $person->getEmail());
    }
}
