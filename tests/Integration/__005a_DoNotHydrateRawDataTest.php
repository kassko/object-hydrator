<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class _005_DoNotHydrateRawDataTest extends TestCase
{
    /**
     * test
     */
    public function letsGo()
    {
        $primaryData = [
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
            'address' => new class(
                '01 Lloyd Road',
                'South Siennaborough',
                'Tonga'
            ) {
                private ?string $street = null;
                private ?string $city = null;
                private ?string $country = null;

                public function __construct(string $street, string $city, string $country) {
                    $this->street = $street;
                    $this->city = $city;
                    $this->country = $country;
                }

                public function getStreet() : ?string { return $this->street; }
                public function getCity() : ?string { return $this->city; }
                public function getCountry() : ?string { return $this->country; }
            },
        ];

        $person = new class(1) {
            private int $id;
            /**
             * @BHY\PropertyConfig\SingleType(keyInRawData="first_name")
             */
            private ?string $firstName = null;
            /**
             * @BHY\PropertyConfig\SingleType(keyInRawData="last_name")
             */
            private ?string $lastName = null;
            /**
             * @BHY\PropertyConfig\SingleType(hydrateRawData=false)
             */
            private ?object $address = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getAddress() : ?object { return $this->address; }
            public function setAddress(object $address) { $this->address = $address; }
        };

        /*
        $faker = \Faker\Factory::create('en_GB');
        echo '['.$faker->streetAddress.']';
        echo '['.$faker->city.']';
        echo '['.$faker->country.']';
        */

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person, $primaryData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertTrue(is_object($person->getAddress()));
        $this->assertSame('01 Lloyd Road', $person->getAddress()->getStreet());
        $this->assertSame('South Siennaborough', $person->getAddress()->getCity());
        $this->assertSame('Tonga', $person->getAddress()->getCountry());
    }
}
