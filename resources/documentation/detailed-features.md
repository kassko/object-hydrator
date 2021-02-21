Details of object hydrator capabilities
===============

## Summary

* [Hydration](#hydration)
    - [Hydrating simply objects from raw data](#hydrating-simply-objects-from-raw-data)
    - [Tuning property mapping](#tuning-property-mapping)
    - [Toggling between auto and explicit property configuration](#toggling-between-auto-and-explicit-property-configuration)
    - [Implicitly configuring all properties to be hydrated but adding exclusions.](#implicitly-configuring-all-properties-to-be-hydrated-but-adding-exclusions.)
    - [Explicitly configuring to be hydrated.](#explicitly-configuring-to-be-hydrated)
    - [Inheriting configurations from parent classes or traits](#inheriting-configurations-from-parent-classes-or-traits)
    - [Hydrating a property object type hinted with a super type - creating candidates configurations for each sub type](#hydrating-a-property-object-type-hinted-with-a-super-type-creating-candidates-configurations-for-each-sub-type)
    - [Hydrating collections](#hydrating-collections)
    - [Hydrating polymorphic collections - using candidates class names](#hydrating-polymorphic-collections-using-candidates-class-names)
* [Data sources](#data-sources)
* [Class metadata configurations](#class-metadata-configurations)
* [Handling value objects or reuse classes using another metadata](#handling-value-objects-or-reuse-classes-using-another-metadata)


*****************
## Hydration

### Hydrating simply objects from raw data
```php
$normalizedData = [
    'id' => 1,
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
];

$person = new class() {
    private $id;
    private $name;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getFirstName() { return $this->firstName; }
    public function setFirstName($name) { $this->firstName = $firstName; }

    public function getLastName() { return $this->lastName; }
    public function setLastName($name) { $this->lastName = $lastName; }
}

$hydrator->hydrate($person, $normalizedData);
```

### Tuning property mapping
```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

$normalizedData = [
    'id' => 1,
    'firstName' => 'Dany',
    'lastName' => 'Gomes',
    'email' => 'Dany@Gomes',
];

$person = new class() {
    private int $id;
    /**
     * @BHY\PropertyConfig\SingleType(keyInRawData="firstName")
     */
    private ?string $firstName = null;
    /**
     * @BHY\PropertyConfig\SingleType(keyInRawData="lastName")
     */
    private ?string $lastName = null;
    private ?string $email = null;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }

    public function getEmail() : ?string { return $this->email; }
    public function setEmail(string $email) { $this->email = $email; }
};

$hydrator->hydrate($person, $normalizedData);
```

```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

$normalizedData = [
    'id' => 1,
    'firstName' => 'Dany',
    'lastName' => 'Gomes',
    'email' => 'Dany@Gomes',
];

/**
 * @BHY\ClassConfig(rawDataKeyStyle="camel")
 */
$person = new class() {
    private int $id;

    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $email = null;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }

    public function getEmail() : ?string { return $this->email; }
    public function setEmail(string $email) { $this->email = $email; }
};

$hydrator->hydrate($person, $normalizedData);

/**
 * @BHY\ClassConfig(rawDataKeyStyle="underscore")
 */
$person = new class() {
}

/**
 * @BHY\ClassConfig(rawDataKeyStyle="dash")
 */
$person = new class() {
}

/**
 * @BHY\ClassConfig(
 *      rawDataKeyStyle="custom",
 *      toRawDataKeyStyleConverter=@BHY\Method(name="mapPropertyNameToKey")
 * )
 */
$person = new class() {
    private function mapPropertyNameToKey(string $propertyName)
    {
        return '_' . $propertyName . '_';//For property firstName, and raw data key _firstName_.
    }
}

/**
 * @BHY\ClassConfig(
 *      rawDataKeyStyle="custom",
 *      toRawDataKeyStyleConverter=@BHY\Method(class="PropertyToKeyStyleMapper", name="mapPropertyNameToKey")
 * )
 */
$person = new class() {
}

class PropertyToKeyStyleMapper
{
    public function mapPropertyNameToKey(string $propertyName)
    {
        return '_' . $propertyName . '_';//For property firstName, and raw data key _firstName_.
    }
}
```

### Toggling between auto and explicit property configuration

#### Implicitly configuring all properties to be hydrated but adding exclusions.
```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

$normalizedData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'email' => 'Dany@Gomes',
];

$person = new class(1) {
    private int $id;
    private ?string $firstName = null;
    private ?string $lastName = null;
    /**
     * @BHY\NotToAutoconfigure
     */
    private ?string $email = null;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }

    public function getEmail() : ?string { return $this->email; }
    public function setEmail(string $email) { $this->email = $email; }
};

$hydrator->hydrate($person, $normalizedData);
```

#### Explicitly configuring to be hydrated.
```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

$normalizedData = [
    'firstName' => 'Dany',
    'lastName' => 'Gomes',
    'email' => 'Dany@Gomes',
];

/**
 * @BHY\ClassOptions(defaultAutoconfigureProperties=false)
 */
$person = new class(1) {
    private int $id;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $firstName = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $lastName = null;
    private ?string $email = null;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }

    public function getEmail() : ?string { return $this->email; }
    public function setEmail(string $email) { $this->email = $email; }
};

$hydrator->hydrate($person, $normalizedData);
```

### Inheriting configurations from parent classes or traits

```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

/**
 * @BHY\ClassOptions(defaultAutoconfigureProperties=false)
 */
abstract class Car
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

class ElectricCar extends Car
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $energyProvider;

    public function getEnergyProvider() : ?string { return $this->energyProvider; }
    public function setEnergyProvider(string $energyProvider) { $this->energyProvider = $energyProvider; }
}
```

```php
use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

trait CarTrait
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

/**
 * @BHY\ClassOptions(defaultAutoconfigureProperties=false)
 */
class ElectricCar
{
    use CarTrait;

    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $energyProvider;

    public function __construct(?int $id = null) { $this->id = $id; }

    public function getEnergyProvider() : ?string { return $this->energyProvider; }
    public function setEnergyProvider(string $energyProvider) { $this->energyProvider = $energyProvider; }
}
```

### Hydrating a property object type hinted with a super type - creating candidates configurations for each sub type

```php
use Big\Hydrator\Annotation\Doctrine as BHY;
use Kassko\ObjectHydratorIntegrationTest\Fixture;

class Garage
{
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\Candidates(candidates={
     *      @BHY\PropertyConfig\SingleType(
     *          class="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car\GasolinePoweredCar",
     *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('car.gasoline_kind')")
     *      ),
     *      @BHY\PropertyConfig\SingleType(
     *          class="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car\ElectricCar",
     *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('car.energy_provider')")
     *      )
     * })
     */
    private ?Car $car = null;

    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getCar() : ?Car { return $this->car; }
    public function setCar(Car $car) { $this->car = $car; }
}

abstract class Car
{
    private ?int $id = null;
    private ?string $brand = null;


    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

class ElectricCar extends Car
{
    private ?string $energyProvider;

    public function getEnergyProvider() : ?string { return $this->energyProvider; }
    public function setEnergyProvider(string $energyProvider) { $this->energyProvider = $energyProvider; }
}

class GasolinePoweredCar extends Car
{
    private ?string $gasolineKind;

    public function getGasolineKind() : ?string { return $this->gasolineKind; }
    public function setGasolineKind(string $gasolineKind) { $this->gasolineKind = $gasolineKind; }
}
```

```php
$garagePrimaryData = [
    'id' => 1,
    'car' => [
        'id' => 1,
        'brand' => 'ford',
        'gasoline_kind' => 'premium',
    ]
];
$garage = new \Garage;
$this->hydrator->hydrate($garage, $garagePrimaryData);
```

```php
$garagePrimaryData = [
    'id' => 1,
    'car' => [
        'id' => 2,
        'brand' => 'fiesta',
        'energy_provider' => 'catenary',
    ]
];
$garage = new \Garage;
$this->hydrator->hydrate($garage, $garagePrimaryData);
```

### Hydrating collections

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];

    public function __construct(string $id) { $this->id = $id; }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function setPassengers(array $passengers) { $this->passengers = $passengers; }
};

$hydrator->hydrate($flight, $primaryData);
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
     */
    private ArrayCollection $passengers;//This a specific collection structure.

    public function addPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers->add($passenger); }
};

$hydrator->hydrate($flight, $primaryData);
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

/**
 * @BHY\ClassConfig(defaultAdderNameFormat="append%sItem")
 */
$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];
    public function appendPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```

```php
/**
 * @BHY\ClassConfig(defaultAdderNameFormat="add%sItem")
 */
$flight = new class('W6047') {
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];
    public function addPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger", adder="addPassenger")
     */
    private array $passengers = [];

    public function addPassenger(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger", adder="addPassenger")
     */
    private array $passengers = [];

    public function addPassenger(string $key, Fixture\Model\Flight\Passenger $passenger) { $this->passengers[$key], $passenger; }
};
```

### Hydrating polymorphic collections - using candidates class names

```php
$primaryData = [
    'cars' => [
        [//an gasoline powered car raw data
            'id' => 1,
            'brand' => 'ford',
            'gasoline_kind' => 'premium',//data specific to gasoline powered car which can be used as discriminator
        ],
        [//an electric car raw data
            'id' => 2,
            'brand' => 'fiesta',
            'energy_provider' => 'catenary',//data specific to electric car which can be used as discriminator
        ]
    ]
];

use Big\Hydrator\Annotation\Doctrine as BHY;

$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('gasoline_kind')")
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car\ElectricCar",
     *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('energy_provider')")
     *          )
     *      )
     * )
     */
    private array $cars = [];//The polymorphic collection which can contains either electric cars or gasoline powered cars.

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }

    public function getCars() : array { return $this->cars; }
    public function addCarsItem(Fixture\Model\Car\Car $car) { $this->cars[] = $car; }
};

$hydrator->hydrate($garage, $primaryData);
```

*****************
## Data sources

*****************
## Class metadata configurations

*****************
## Handling value objects or reuse classes using another metadata
