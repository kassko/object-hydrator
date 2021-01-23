Object hydrator
==================

An object hydrator for php 7.4/8.0.
This library was created to facilite to create object models from complex data models.

Note that this library replaces kassko/data-mapper.
Differences are the followings:
* a more appropriate naming - `object hydrator`
* a library which works with php 7.4/8.0 - kassko/data-mapper works with php <= 7.3
* many more features to facilite to create object models

Here are the following main features:
* Hydration
  * hydrate objects from raw data
  * hydrate nested objects
  * hydrate properties inherited from classes or imported from traits
  * hydrate collections
  * hydrate polymorphic collections
* Data source
  * hydrate properties from specific data sources
  * hydrate lazily properties - only when its getter is called
  * hydrate properties from the join of multiples datasources
  * use expressions to create complex joins of datasources
* Configuration
  * control hydration behaviour through configurations
  * choose configurations formats among Doctrine annotations, yaml, php or provide your format
  * use multiples configuration formats - by objects
  * create candidates configurations and let the hydrator choose at runtime the good configuration
  * create configuration containing dynamic values - expressions/methods to be evaluated at runtime



## Install

```bash
composer require kassko/object-hydrator:^1.0
composer install
```

If you use Symfony Php framework, register to kernel the following bundle
`Kassko\ObjectHydrator\FrameworkBridge\Symfony\KasskoObjectHydratorBundle`

## Hydrates objects from normalized data

### Perform basic object hydration

```php
use Big\Hydrator\HydratorBuilder;

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

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($person, $normalizedData);
```
By default, raw data keys must be in dash case format to be mapped to their related camel cases properties.
But you can change the raw data key format.
You must add a property annotation `PropertyConfig` for type scalars `firstName` and `lastName` properties to map `PropertyConfig\SingleType`.

### Map properties names to keys in normalized data
```php
use Big\Hydrator\HydratorBuilder;

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

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($person, $normalizedData);

$this->assertSame(1, $person->getId());
$this->assertSame('Dany', $person->getFirstName());
$this->assertSame('Gomes', $person->getLastName());
$this->assertSame('Dany@Gomes', $person->getEmail());
```

But this camel case is a well known case like others.
So you have built-in raw data cases.

### Map all properties names to keys at once with built-in raw data key case
```php
/**
 * @BHY\ClassConfig(rawDataKeyStyle="camel")
 */
$person = new class() {
}
```

```php
/**
 * @BHY\ClassConfig(rawDataKeyStyle="underscore")
 */
$person = new class() {
}
```

```php
/**
 * @BHY\ClassConfig(rawDataKeyStyle="dash")
 */
$person = new class() {
}
```

For advanced needs, you can use a specific mapper.

### Map properties names to keys with a specific mapper

```php
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
```

When the map method is in the object as above, it can be private to avoid exposing hydration configuration logic outside the object.
This map method can be outside the object but public, in fact where you want.

```php
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

By default, properties are all hydrated.
To exclude a property from automatic hydration, you can go through the code below.

### Default hydrate all properties except those specified
```php
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

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($person, $normalizedData);
```

At the opposite, you may want by default not to hydrate any property except a few specified.

### Default hydrate no property except those specified
```php
use Big\Hydrator\HydratorBuilder;
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

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($person, $normalizedData);
```

### Hydrate properties of a parent class too and inherit this parent class configuration
```php
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

### Hydrate properties of a used trait and import this trait configuration
```php
trait CarTrait
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}
```

```php
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

### Hydrate a property object type hinted with a super type

```php
$gasolinePoweredCarPrimaryData = [
    'id' => 1,
    'car' => [
        'id' => 1,
        'brand' => 'ford',
        'gasoline_kind' => 'premium',
    ]
];

$electricCarPrimaryData = [
    'id' => 1,
    'car' => [
        'id' => 2,
        'brand' => 'fiesta',
        'energy_provider' => 'catenary',
    ]
];
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;
use Big\HydratorTest\Integration\Fixture;

class Garage
{
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\Candidates(candidates={
     *      @BHY\PropertyConfig\SingleType(
     *          class="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('car.gasoline_kind')")
     *      ),
     *      @BHY\PropertyConfig\SingleType(
     *          class="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
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
```

```php
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
$primaryData = [
    'id' => 1,
    'car' => [
        'id' => 1,
        'brand' => 'ford',
        'gasoline_kind' => 'premium',
    ]
];

$garage = new Fixture\Model\Car\Garage;

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($garage, $primaryData);

$this->assertSame(1, $garage->getId());
$this->assertInstanceOf(Fixture\Model\Car\GasolinePoweredCar::class, $garage->getCar());
$this->assertSame(1, $garage->getCar()->getId());
$this->assertSame('premium', $garage->getCar()->getGasolineKind());



$primaryData = [
    'id' => 1,
    'car' => [
        'id' => 2,
        'brand' => 'fiesta',
        'energy_provider' => 'catenary',
    ]
];

$garage = new Fixture\Model\Car\Garage;

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($garage, $primaryData);

$this->assertSame(1, $garage->getId());
$this->assertInstanceOf(Fixture\Model\Car\ElectricCar::class, $garage->getCar());
$this->assertSame(2, $garage->getCar()->getId());
$this->assertSame('catenary', $garage->getCar()->getEnergyProvider());
```

## Hydrate collections from normalized data

### Set hydrated items of a collection with a setter

```php
$primaryData = [
    'passengers' => [
        [
            'id' => 1,
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
        ],
        [
            'id' => 2,
            'first_name' => 'Bogdan',
            'last_name' => 'Vassilescu',
        ],
    ],
];

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];

    public function __construct(string $id) { $this->id = $id; }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function setPassengers(array $passengers) { $this->passengers = $passengers; }
};

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($flight, $primaryData);
```

Maybe you prefer use an adder to control each hydrated item added to the collection or to add them to a specific collection structure (ie: Doctrine ArrayCollection).

### Add hydrated items of a collection with an adder

```php
use Big\Hydrator\Annotation\Doctrine as BHY;
use Big\Hydrator\HydratorBuilder;
use Doctrine\Common\Collections\ArrayCollection;

$primaryData = [
    'passengers' => [
        [
            'id' => 1,
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
        ],
        [
            'id' => 2,
            'first_name' => 'Bogdan',
            'last_name' => 'Vassilescu',
        ],
    ],
];

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger")
     */
    private ArrayCollection $passengers = [];//This a specific collection structure.

    public function __construct(string $id) {
        $this->id = $id;
        $this->passengers = new ArrayCollection;
    }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function addPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers->add($passenger); }
};

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($flight, $primaryData);
```

Default adder name is "addPassengersItem", it follows template "add%sItem", where "%s" is the collection property name with the first letter upper cased.
The code below configure the adder through a template and has the same effect.

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

/**
 * @BHY\ClassConfig(defaultAdderNameFormat="append%sItem")
 */
$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];

    public function __construct(string $id) { $this->id = $id; }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function appendPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```

And so you can change the adder template.
```php
use Big\Hydrator\Annotation\Doctrine as BHY;

/**
 * @BHY\ClassConfig(defaultAdderNameFormat="add%sItem")
 */
$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger")
     */
    private array $passengers = [];

    public function __construct(string $id) { $this->id = $id; }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function addPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```
Adders of a class often follows the same naming convention, through the way above, we configure at once adder name.

But if you don't have a convention, you can specify the adder by property.

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger", adder="addPassenger")
     */
    private array $passengers = [];

    public function __construct(string $id) { $this->id = $id; }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function addPassenger(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
};
```

* adder by property takes precedence over adder template
* and adder template takes priority over setter

Note that, an adder can takes too 2 arguments (key and item).

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$flight = new class('W6047') {
    private string $id;
    /**
     * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger", adder="addPassenger")
     */
    private array $passengers = [];

    public function __construct(string $id) {
        $this->id = $id;
    }

    public function getId() : string { return $this->id; }

    public function getPassengers() : array { return $this->passengers; }
    public function addPassenger(string $key, Fixture\Model\Flight\Passenger $passenger) { $this->passengers[$key], $passenger; }
};
```

### Hydrate objects of a polymorphic collection
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
```

```php
use Big\Hydrator\Annotation\Doctrine as BHY;

$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('gasoline_kind')")
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
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

```

Note several things
* usage of candidates configurations
* usage of dicriminator expressions to decide at on moment which candidate configuration is good
* usage of a built-in function "rawItemDataKeyExists" to check if a key exists in the raw array data of an item of the collection

To know more about candidataes configurations, please take a look here ...
To know more about expression configuration, please take a look here ...
To know more about available functions in expression language, please take a look here ...

Then the code below should work fine.

```php
use Big\Hydrator\HydratorBuilder;
use Big\HydratorTest\Integration\Fixture;

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($garage, $primaryData);

$this->assertSame(1, $garage->getId());

$cars = $garage->getCars();
$this->assertCount(2, $cars);

$car = $cars[0];
$this->assertInstanceOf(Fixture\Model\Car\GasolinePoweredCar::class, $car);
$this->assertSame(1, $car->getId());
$this->assertSame('ford', $car->getBrand());
$this->assertSame('premium', $car->getGasolineKind());

$car = $cars[1];
$this->assertInstanceOf(Fixture\Model\Car\ElectricCar::class, $car);
$this->assertSame(2, $car->getId());
$this->assertSame('fiesta', $car->getBrand());
$this->assertSame('catenary', $car->getEnergyProvider());
```

## Hydrate properties from data sources

### Hydrate one of properties from a specified data source

```php
namespace Big\HydratorTest\Integration\Fixture\Service;

class EmailService
{
    public function getData($id)
    {
        switch ($id) {
            case 1:
                return 'dany@gomes';
            case 2:
                return 'bogdan@vassilescu';
        }
    }
}
```

```php
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
     *              class="Big\HydratorTest\Integration\Fixture\Service\EmailService",
     *              name="getData",
     *              args={"#id"},
     *              indexedByPropertiesKeys=false
     *          )
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
    public function setEmail(string $email) { $this->email = $email; }
};

$hydrator = (new HydratorBuilder())->build();
$hydrator->hydrate($person, $rawData);//Only hydrate properties firstName and lastName. email will be lazy hydrated/loaded.

//A call to getMail() will trigger hydration of property email. Of course, hydration will be triggered only once.
$person->getEmail();
```

`indexedByPropertiesKeys` is default to true.

Value must be false if raw data are not indexed by the key or keys of properties to map value(s).

`indexedByPropertiesKeys` must be set to false with such raw data.
```php
public function getData($id)
{
    switch ($id) {
        case 1:
            return 'dany@gomes';
        case 2:
            return 'bogdan@vassilescu';
    }
}
```

But it must not be set or set to true (the default value) with such raw data.
```php
public function getData($id)
{
    switch ($id) {
        case 1:
            return ['email' => 'dany@gomes'];
        case 2:
            return ['email' => 'bogdan@vassilescu'];
    }
}
```

In fact, when the raw data contains data for several keys (which is the main use case)
```php
public function getData($id)
{
    switch ($id) {
        case 1:
            return [
                'first_name' => 'Dany',
                'last_name' => 'Gomes',
                'name' => 'Christian',
                'phone' => '01 02 03 04 05',
            ];
        case 2:
            return [
                'first_name' => 'Bogdan',
                'name' => 'Vassilescu',
                'phone' => '01 06 07 08 09',
            ];
    }
}
```
then it is obviously indexed by these keys, that's why `indexedByPropertiesKeys` is set by default to true.

Note that you can load a property configured with data source immediately by specifying the loading mode "eager".

This is usefull in particular cases, for example when properties are publics and accessed directly instead of via getters which wrapp the load triggering logic `loadProperty()`.

You can choose the hydration/loading mode between "lazy" (default mode) and "eager".
```php
/**
     * @BHY\PropertyConfig\SingleType(
     *      dataSource=@BHY\DataSource(
     *          method=@BHY\Method(
     *              class="Big\HydratorTest\Integration\Fixture\Service\EmailService",
     *              name="getData",
     *              args={"#id"},
     *              indexedByPropertiesKeys=false
     *          ),
     *          loadingMode="eager"
     *      )
     *  )
     */
    private ?string $email = null;
```

Default loading mode is "lazy" and this is not needed to specify it.
```php
/**
     * @BHY\PropertyConfig\SingleType(
     *      dataSource=@BHY\DataSource(
     *          method=@BHY\Method(
     *              class="Big\HydratorTest\Integration\Fixture\Service\EmailService",
     *              name="getData",
     *              args={"#id"},
     *              indexedByPropertiesKeys=false
     *          ),
     *          loadingMode="lazy"
     *      )
     *  )
     */
    private ?string $email = null;
```

### Use a data source methods (more details)

### Share a datasource

The data source below is configured in line. If you have severals properties to hydrate from a same data source
you can share it.

Share one data source
```php
namespace Big\HydratorTest\Integration\Fixture\Service;

class PersonService
{
    public function getData($id)
    {
        switch ($id) {
            case 1:
                return [
                    'first_name' => 'Dany',
                    'last_name' => 'Gomes',
                    'name' => 'Christian',
                    'phone' => '01 02 03 04 05',
                ];
            case 2:
                return [
                    'first_name' => 'Bogdan',
                    'name' => 'Vassilescu',
                    'phone' => '01 06 07 08 09',
                ];
        }
    }
}

class EmailService
{
    public function getData($id)
    {
        switch ($id) {
            case 1:
                return 'dany@gomes';
            case 2:
                return 'bogdan@vassilescu';
        }
    }
}
```

```php
/**
     * @test
     */
    public function letsGo()
    {
        /**
         * @BHY\DataSource(
         *      id="person",
         *      method=@BHY\Method(
         *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
         *          name="getData",
         *          args={"#id"}
         *      )
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
            /**
             * @BHY\PropertyConfig\SingleType(
             *      dataSource=@BHY\DataSource(
             *          method=@BHY\Method(
             *              class="Big\HydratorTest\Integration\Fixture\Service\EmailService",
             *              name="getData",
             *              args={"#id"},
             *              indexedByPropertiesKeys=false
             *          )
             *      )
             *  )
             */
            private ?string $email = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { $this->loadProperty('firstName'); return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { $this->loadProperty('lastName'); return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getPhone() : ?string { $this->loadProperty('phone'); return $this->phone; }
            public function setPhone(string $phone) { $this->phone = $phone; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($person);
```

Triggering loading of one property among `firstName`, `lastName`, `phone` will load the 3 properties, because they are configured with the same data source. Loading will not be triggered a second time when triggering from another of these properties.

You also can share multiples data sources. To know more, please take a look at part related to sharing property configuration across several properties.

## Handle properties with same class and diffrent keys in raw data

Sometimes, particularly in case of valueobjects, raw data keys you need to map to a class property depend on multiple properties.

You can define the mapping on the parent property configuration.
```php
class AddressSimple
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;
}

$rawData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'billing_street' => '01 Lloyd Road',
    'billing_city' => 'South Siennaborough',
    'billing_country' => 'Tonga',
    'delivery_street' => '12 Lloyd Road',
    'delivery_city' => 'North Siennaborough',
    'delivery_country' => 'Tonga',
];

$person = new class(1) {
    private int $id;
    private ?string $firstName = null;
    private ?string $lastName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingValues={"billing_street": "street", "billing_city": "city", "billing_country": "country"}
     *      )
     * )
     */
    private ?AddressSimple $billingAddress = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingValues={"delivery_street": "street", "delivery_city": "city", "delivery_country": "country"}
     *      )
     * )
     */
    private ?AddressSimple $deliveryAddress = null;
};
```

If keys of raw data related to the parent property configuration have a prefix, you can do this
```php
class AddressSimple
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;
}

$rawData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'billing_street' => '01 Lloyd Road',
    'billing_city' => 'South Siennaborough',
    'billing_country' => 'Tonga',
    'delivery_street' => '12 Lloyd Road',
    'delivery_city' => 'North Siennaborough',
    'delivery_country' => 'Tonga',
];

$person = new class(1) {
    private int $id;
    private ?string $firstName = null;
    private ?string $lastName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingPrefix="billing_"
     *      )
     * )
     */
    private ?AddressSimple $billingAddress = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingPrefix="delivery_"
     *      )
     * )
     */
    private ?AddressSimple $deliveryAddress = null;
};
```

This is another way to do
```php
class AddressSimple
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;
}

$rawData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'billing_street' => '01 Lloyd Road',
    'billing_city' => 'South Siennaborough',
    'billing_country' => 'Tonga',
    'delivery_street' => '12 Lloyd Road',
    'delivery_city' => 'North Siennaborough',
    'delivery_country' => 'Tonga',
];

$person = new class(1) {
    private int $id;
    private ?string $firstName = null;
    private ?string $lastName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingMethod=@BHY\Method(name="mapBillingAddressKey")
     *      )
     * )
     */
    private ?AddressSimple $billingAddress = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressSimple",
     *      rawDataLocation=@BHY\RawDataLocation(
     *          locationName="parent",
     *          keysMappingMethod=@BHY\Method(name="mapDeliveryAddressKey")
     *      )
     * )
     */
    private ?AddressSimple $deliveryAddress = null;

    public function mapBillingAddressKey(string $key) {
        return false !== strpos($key, 'billing_') ? substr($key, strlen('billing_')) : null;
    }

    public function mapDeliveryAddressKey(string $key) {
        return false !== strpos($key, 'delivery_') ? substr($key, strlen('delivery_')) : null;
    }
```

## Use a factory method

Sometimes, particularly in case of value object. The way to construct an instance of a class is not the constructor but a factory method.
```php
class AddressWithFactoryMethod
{
  private ?string $street = null;
  private ?string $city = null;
  private ?string $country = null;

  public static function from(?string $street = null, ?string $city = null, ?string $country = null) : self
  {
      return new self($street, $city, $country);
  }

  private function __construct(?string $street = null, ?string $city = null, ?string $country = null)
  {
      $this->street = $street;
      $this->city = $city;
      $this->country = $country;
  }
}

$rawData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'billing_address' => [
        'street' => '01 Lloyd Road',
        'city' => 'South Siennaborough',
        'country' => 'Tonga',
    ],
    'delivery_address' => [
        'street' => '12 Lloyd Road',
        'city' => 'North Siennaborough',
        'country' => 'Tonga',
    ],
];

$person = new class(1) {
    private int $id;
    private ?string $firstName = null;
    private ?string $lastName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressWithFactoryMethod",
     *      factoryMethodName="from"
     * )
     */
    private ?AddressWithFactoryMethod $billingAddress = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      class="\Big\HydratorTest\Integration\Fixture\Model\Address\AddressWithFactoryMethod",
     *      factoryMethodName="from"
     * )
     */
    private ?AddressWithFactoryMethod $deliveryAddress = null;
}
```

## Share property configuration across several properties

### Share datasource

Share one data source
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      )
   * )
   */
  $person = new class(1) {
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
  }
```

Share multiples data sources
```php
/**
   * @BHY\DataSources(
   *    @BHY\DataSource(
   *        id="person",
   *        method=@BHY\Method(
   *            class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *            name="getData",
   *            args={"#id"}
   *        )
   *    ),
   *    @BHY\DataSource(
   *        id="email",
   *        method=@BHY\Method(
   *            class="Big\HydratorTest\Integration\Fixture\Service\EmailService",
   *            name="getData",
   *            args={"#id"},
   *            indexedByPropertiesKeys=false
   *        )
   *    )
   * )
   */
  $person = new class(1) {
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
      /**
       * @BHY\PropertyConfig\SingleType(dataSourceRef="email")
       */
      private ?string $email = null;
  }
```

### Share one expression
```php
$primaryData = [
    'cars' => [
        [
            'id' => 1,
            'brand' => 'ford',
            'gasoline_kind' => 'premium',
        ],
        [
            'id' => 2,
            'brand' => 'fiesta',
            'energy_provider' => 'catenary',
        ]
    ]
];

/**
 * @BHY\Expression(id="gasoline_powered_car", value="rawItemDataKeyExists('gasoline_kind')")
 */
$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminator_expression_ref="gasoline_powered_car"
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
     *              discriminator_expression=@BHY\Expression(value="rawItemDataKeyExists('energy_provider')")
     *          )
     *      )
     * )
     */
    private array $cars = [];
};
```

### Share multiples expressions
```php
/**
  * @BHY\Methods(
  *      @BHY\Method(id="gasoline_powered_car", value="rawItemDataKeyExists('gasoline_kind')"),
  *      @BHY\Method(id="electric_car", value="rawItemDataKeyExists('energy_provider')")
  * )
  */
$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminator_expression_ref="gasoline_powered_car"
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
     *              discriminator_expression_ref="electric_car")
     *          )
     *      )
     * )
     */
    private array $cars = [];
};
```

### Share one method

Actually, shared methods cans only be used on as discriminator, and so neither as data sources method nor as raw data key style converter.
```php
/**
 * @BHY\Method(id="gasoline_powered_car", class="classa", name="metha"),
 */
$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminator_method_ref="gasoline_powered_car"
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
     *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('energy_provider')")
     *          )
     *      )
     * )
     */
    private array $cars = [];
};
```

### Share multiples methods
```php
/**
  * @BHY\Methods(
  *      @BHY\Method(id="gasoline_powered_car", name="rawDataBelongsToGasolinePoweredCar"),
  *      @BHY\Method(id="electric_car", class="Big\HydratorTest\Integration\Fixture\Service\RawDataDiscriminatorService", name="rawDataBelongsToElectricCar")
  * )
  */
$garage = new class(1) {
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\CollectionType(
     *      itemClassCandidates=@BHY\ItemClassCandidates(
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *              discriminator_method_ref="gasoline_powered_car"
     *          ),
     *          @BHY\ItemClassCandidate(
     *              value="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
     *              discriminator_method_ref="electric_car"
     *          )
     *      )
     * )
     */
    private array $cars = [];
};
```

## Use candidates configurations
See example above
* with property type hinted with super type
* with polymorphic collection

## Use advanced features

### Use dynamic attributes
Please look example related to value object hydration

### Use a data source with fallbacks
```php
```

### Use a data source with providers
```php
```

### Tag data sources and retrieve them by tag in expression (ex: to do some computations like merging them)

### Use a data source with loading scope

Given the following:
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      )
   * )
   */
  $person = new class(1) {
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
  }

  $hydrator = (new HydratorBuilder())->build();
  $hydrator->hydrate($person);
```

Calling `$person->getFirstName()` will hydrate properties `firstName`, `lastName` and `phone`.
Because triggering loading on a property hydrate this property and all others configured with the same data source.

You can limit the hydration scope.

You can load only the property on which loading is triggered.
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      ),
   *      loadingScope="property"
   * )
   */
  $person = new class(1) {
  }
```

Or load some of the properties that are configured with the same data source.
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      ),
   *      loadingScope="data_source_only_keys",
   *      loadingScopeKeys={"first_name", "last_name"}
   * )
   */
  $person = new class(1) {
  }
```

Or exclude from loading some of these properties.
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      ),
   *      loadingScope="data_source_except_keys",
   *      loadingScopeKeys={"phone"}
   * )
   */
  $person = new class(1) {
  }
```

When it is not specified, `loadingScope` has the default value `data_source`.
```php
 /**
   * @BHY\DataSource(
   *      id="person",
   *      method=@BHY\Method(
   *          class="Big\HydratorTest\Integration\Fixture\Service\PersonService",
   *          name="getData",
   *          args={"#id"}
   *      ),
   *      loadingScope="data_source"
   * )
   */
  $person = new class(1) {
  }
```

### Retrieve a data source in expression and use its result

### Broadcast a context to identify a specificity about where come from an object to hydrate

### Retrieve parent hierarchy of an object in expression


## Details on expressions

### Use advanced built-in expressions
* `this`
* `rawData('raw_data_key')` extract a value from current raw data used for object hydration
* `variables()` return an associative array of all variables (key and values) actually broadcasted
* `property('propName')` return a property value using the getter if it exists
* `directProperty('propName')` return a property value without using the getter
* `service('service_id')` return a service
* `source(data_source_id)` fetch and return content of a data source
* `sourceByTag(data_source_tag)` fetch data sources by tags and return an associative array of multiples data sources raw data indexed by data source id
* `variable('variable_key')` return content of a variable actually broadcasted
* `rawDataKeyExists('key_or_dot_path)` ensure a key 'key' or flatten dot path 'key.nested_key' exists in current raw data used for object hydration
* `rawDataIsSet('key_or_dot_path')`
* `rawDataIsCollectionOfItems()` ensure raw data is a collection of items. This is mainly used to know if we are about to update a collection or not
* `rawDataHasPair('key_or_dot_path', 'value')` ensure raw data contains a pair (key_or_dot_path/value) with no strict comparison
* `rawDataHasPairStrict('key_or_dot_path', 'value')` ensure raw data contains a pair (key_or_dot_path/value) with strict comparison
* `rawDataKeysExists('key_or_dot_path1', 'key_or_dot_path1')` test if severals keys or dot path exists
* `rawItemDataKeyExists()`
* `rawItemDataIsSet()`
* `rawItemDataIsCollectionOfItems()`
* `rawItemDataHasPair()`
* `rawItemDataHasPairStrict()`
* `rawItemDataKeysExists()`


### Provide your own expressions
```php
```

### Provide runtime variables to expression language through UserExpressionContext
```php
```

## Change on runtime metadata, raw data, model object with callbacks events

## Use traverser to help to implement visitors (ie: to create data source dependency graph etc.)
