Hydrator
==================

An hydrator for php 7/8.

This library hydrates objects from raw data and hydrate properties or fetch annotated lazyloaded datasource services

This is an hydrator which allows to
* object hydration from raw data
* nested hydration
* hydration behaviour configuration via annotations and other format and custom format
* dynamic values in configuration from expressions or service methods evaluation
* configuration resolved at runtime depending on candidates configuration ane expressions
* hydration from fetched raw data of services configured

## Install

```sh
composer require kassko-hydrator:^1.0
composer install
```

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

### Map properties names to keys in normalized data
```php
use Big\Hydrator\HydratorBuilder;

$normalizedData = [
    'id' => 1,
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'email' => 'Dany@Gomes',
];

$person = new class() {
    private int $id;
    /**
     * @BHY\Property(keyInRawData="first_name")
     */
    private ?string $firstName = null;
    /**
     * @BHY\Property(keyInRawData="last_name")
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

By default, properties are all hydrated. You can configure this.

### Default hydrate all properties except those specified
```php
$normalizedData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'email' => 'Dany@Gomes',
];

$person = new class(1) {
    private int $id;
    /**
     * @BHY\Property(keyInRawData="first_name")
     */
    private ?string $firstName = null;
    /**
     * @BHY\Property(keyInRawData="last_name")
     */
    private ?string $lastName = null;
    /**
     * @BHY\ExcludedProperty
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

### Default hydrate no property except those specified

```php
use Big\Hydrator\HydratorBuilder;
$normalizedData = [
    'first_name' => 'Dany',
    'last_name' => 'Gomes',
    'email' => 'Dany@Gomes',
];

/**
 * @BHY\ClassOptions(defaultHydrateAllProperties=false)
 */
$person = new class(1) {
    private int $id;
    /**
     * @BHY\Property(keyInRawData="first_name")
     */
    private ?string $firstName = null;
    /**
     * @BHY\Property(keyInRawData="last_name")
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
