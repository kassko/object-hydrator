<?php

namespace Kassko\ObjectHydratorTest\Integration\Fixture\Model\Person;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

/**
 * @BHY\ClassConfig(
 *      rawDataKeyStyle="custom",
 *      rawDataKeyStyleConverter=@BHY\Method(
 *          class="Kassko\ObjectHydratorTest\Integration\Fixture\Model\Person\PersonRawDataKeyStyleConverter",
 *          name="mapPropertyNameToKey"
 *      )
 * )
 */
class PersonRawDataKeyStyleConverter
{
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

    public static function mapPropertyNameToKey(string $propertyName) {
        return '_' . $propertyName . '_';
    }
}
