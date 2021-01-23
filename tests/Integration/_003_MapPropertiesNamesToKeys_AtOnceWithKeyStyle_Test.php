<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class _003_MapPropertiesNamesToKeys_AtOnceWithKeyStyle_Test extends TestCase
{
    /**
     * @test
     */
    public function camelCase()
    {
        $rawData = [
            'firstName' => 'Dany',
            'lastName' => 'Gomes',
            'email' => 'Dany@Gomes',
        ];

        /**
         * @BHY\ClassConfig(rawDataKeyStyle="camel")
         */
        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
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
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }

    /**
     * @test
     */
    public function underscoreCase()
    {
        $rawData = [
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
            'email' => 'Dany@Gomes',
        ];

        /**
         * @BHY\ClassConfig(rawDataKeyStyle="underscore")
         */
        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
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
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }

    /**
     * @test
     */
    public function dashCase()
    {
        $rawData = [
            'first-name' => 'Dany',
            'last-name' => 'Gomes',
            'email' => 'Dany@Gomes',
        ];

        /**
         * @BHY\ClassConfig(rawDataKeyStyle="dash")
         */
        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
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
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }

    /**
     * @test
     */
    public function customCase_innerConverterMethod()
    {
        $rawData = [
            '_firstName_' => 'Dany',
            '_lastName_' => 'Gomes',
            '_email_' => 'Dany@Gomes',
        ];


        $person = new \Kassko\ObjectHydratorTest\Integration\Fixture\Model\Person\PersonRawDataKeyStyleConverter(1);

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }

    /**
     * @test
     */
    public function customCase_outerConverterMethod()
    {
        $rawData = [
            '_firstName_' => 'Dany',
            '_lastName_' => 'Gomes',
            '_email_' => 'Dany@Gomes',
        ];

        /**
         * @BHY\ClassConfig(
         *      rawDataKeyStyle="custom",
         *      rawDataKeyStyleConverter=@BHY\Method(
         *          class="Kassko\ObjectHydratorTest\Integration\Fixture\Service\PropertyToKeyStyleMapper",
         *          name="mapPropertyNameToKey"
         *      )
         * )
         */
        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
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
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('Dany@Gomes', $person->getEmail());
    }
}
