<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder, ObjectExtension\LoadableTrait};
use Kassko\ObjectHydrator\ClassMetadata\Model;
use Kassko\ObjectHydrator\Observer\Dto;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _031_Hydrate_EventCallbacks_UsingMetadata extends TestCase
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
        $rawData = [
            '_FIRST_NAME_' => 'Dany',
            'last_name' => 'Gomes'
        ];

        $person = new Person(1);
        $person::$asserter = $this;

        $this->hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());
        $this->assertSame('dany@gomes', $person->getEmail());

        $this->assertArrayHasKey('propertyBeforeUsingMetadata', $person::$calls);
        $this->assertArrayHasKey('propertyAfterUsingMetadata', $person::$calls);

        $this->assertArrayHasKey('propertyBeforeUsingMetadataOne', $person::$calls);
        $this->assertArrayHasKey('propertyAfterUsingMetadataOne', $person::$calls);

        $this->assertArrayHasKey('propertyBeforeUsingMetadataTwo', $person::$calls);
        $this->assertArrayHasKey('propertyAfterUsingMetadataTwo', $person::$calls);

        $this->assertArrayHasKey('dataSourceBeforeUsingMetadata', $person::$calls);
        $this->assertArrayHasKey('dataSourceAfterUsingMetadata', $person::$calls);
    }
}

class Person
{
    use LoadableTrait;

    public static array $calls = [];
    public static TestCase $asserter;

    private int $id;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      callbacksUsingMetadata=@BHY\Callbacks(
     *          before=@BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyBeforeUsingMetadata"),
     *          after=@BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyAfterUsingMetadata")
     *      )
     * )
     */
    private ?string $firstName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      callbacksUsingMetadata=@BHY\Callbacks(
     *          beforeCollection=@BHY\Methods(
     *              @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyBeforeUsingMetadataOne"),
     *              @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyBeforeUsingMetadataTwo")
     *          ),
     *          afterCollection=@BHY\Methods(
     *              @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyAfterUsingMetadataOne"),
     *              @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="propertyAfterUsingMetadataTwo")
     *          )
     *      )
     * )
     */
    private ?string $lastName = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      dataSource=@BHY\DataSource(
     *          method=@BHY\Method(
     *              class="Kassko\ObjectHydratorIntegrationTest\Fixture\Service\EmailService",
     *              name="getData",
     *              args={"#id"}
     *          ),
     *          indexedByPropertiesKeys=false,
     *          callbacksUsingMetadata=@BHY\Callbacks(
     *              before=@BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceBeforeUsingMetadata"),
     *              after=@BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceAfterUsingMetadata")
     *          )
     *      )
     *  )
     */
    private ?string $email = null;
    /**
     * @BHY\PropertyConfig\SingleType(
     *      dataSource=@BHY\DataSource(
     *          method=@BHY\Method(
     *              class="Kassko\ObjectHydratorIntegrationTest\Fixture\Service\EmailService",
     *              name="getData",
     *              args={"#id"}
     *          ),
     *          indexedByPropertiesKeys=false,
     *          callbacksUsingMetadata=@BHY\Callbacks(
     *              beforeCollection=@BHY\Methods(
     *                  @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceBeforeUsingMetadataOne"),
     *                  @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceBeforeUsingMetadataTwo")
     *              ),
     *              afterCollection=@BHY\Methods(
     *                  @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceAfterUsingMetadataOne"),
     *                  @BHY\Method(class="Kassko\ObjectHydratorIntegrationTest\Person", name="dataSourceAfterUsingMetadataTwo")
     *              )
     *          )
     *      )
     *  )
     */
    private ?string $secondEmail = null;

    public function __construct(int $id) { $this->id = $id; }

    public function getId() : int { return $this->id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }

    public function getEmail() : ?string { $this->loadProperty('email'); return $this->email; }
    public function setEmail(string $email) { $this->email = $email; }


    public static function classBeforeUsingMetadata(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function classAfterUsingMetadata(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function classBeforeUsingMetadataOne(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function classAfterUsingMetadataOne(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function classBeforeUsingMetadataTwo(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function classAfterUsingMetadataTwo(Dto\Class_\AfterUsingMetadata $dto)
    {
        $classMetadata = $dto->getClassMetadata();

        self::$asserter->assertSame(Person::class, $classMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }


    public static function propertyBeforeUsingMetadata(Dto\Property\BeforeUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('firstName', $propertyMetadata->getName());

        if ($containingClassName === Person::class && $propertyMetadata->getName() === 'firstName') {
            $propertyMetadata->setKeyInRawData('_FIRST_NAME_');
        }

        self::$calls[__FUNCTION__] = 1;
    }

    public static function propertyAfterUsingMetadata(Dto\Property\AfterUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('firstName', $propertyMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function propertyBeforeUsingMetadataOne(Dto\Property\BeforeUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('lastName', $propertyMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function propertyAfterUsingMetadataOne(Dto\Property\AfterUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('lastName', $propertyMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function propertyBeforeUsingMetadataTwo(Dto\Property\BeforeUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('lastName', $propertyMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function propertyAfterUsingMetadataTwo(Dto\Property\AfterUsingMetadata $dto)
    {
        $propertyMetadata = $dto->getPropertyMetadata();
        $containingClassName = $dto->getContainingClassName();

        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('lastName', $propertyMetadata->getName());

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceBeforeUsingMetadata(Dto\DataSource\BeforeUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceAfterUsingMetadata(Dto\DataSource\AfterUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceBeforeUsingMetadataOne(Dto\DataSource\BeforeUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceAfterUsingMetadataOne(Dto\DataSource\AfterUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceBeforeUsingMetadataTwo(Dto\DataSource\BeforeUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }

    public static function dataSourceAfterUsingMetadataTwo(Dto\DataSource\AfterUsingMetadata $dto)
    {
        $dataSourceMetadata = $dto->getDataSourceMetadata();
        $containingClassName = $dto->getContainingClassName();
        $propertyName = $dto->getContainingPropertyName();

        self::$asserter->assertSame('getData', $dataSourceMetadata->getMethod()->getName());
        self::$asserter->assertSame(Person::class, $containingClassName);
        self::$asserter->assertSame('email', $propertyName);

        self::$calls[__FUNCTION__] = 1;
    }
}
