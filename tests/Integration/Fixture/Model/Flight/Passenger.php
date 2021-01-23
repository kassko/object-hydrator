<?php

namespace Kassko\ObjectHydratorTest\Integration\Fixture\Model\Flight;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

class Passenger
{
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType(keyInRawData="first_name")
     */
    private ?string $firstName = null;
    /**
     * @BHY\PropertyConfig\SingleType(keyInRawData="last_name")
     */
    private ?string $lastName = null;

    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }
}
