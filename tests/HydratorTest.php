<?php

namespace Big\HydratorTest;

use Big\Hydrator\{ClassMetadata as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class HydratorTest extends TestCase
{
    /**
     * @test
     */
    public function basic()
    {
        $this->assertTrue(true);
    }

    /**
     * test
     */
    public function basic2()
    {
        $ids = [1, 2];

        foreach ($ids as $id) {
            /**
             * @BHY\DataSources({
             *      @BHY\DataSource(
             *          id="nameSource",
             *          method=@BHY\Method(class="Big\HydratorTest\Fixture\PersonService", name="getData", args={"#id"}),
             *          indexedByPropertiesKeys=true
             *      ),
             *      @BHY\DataSource(
             *          id="emailSource",
             *          method=@BHY\Method(class="Big\HydratorTest\Fixture\EmailService", name="getData", args={"#id"})
             *      )
             * })
             *
             *
             * @BHY\Conditionals({
             *      @BHY\Conditional(
             *          id="lazyLoadableCond",
             *          value=@BHY\Expression(value="object.getId() == 1")
             *      ),
             *      @BHY\Conditional(
             *          id="privateEmailCond",
             *          value=@BHY\Method(
             *              class="Big\HydratorTest\Fixture\MailConditionnalService",
             *              name="isPrivateMail"
             *          )
             *      )
             * })
             */
            $person = new class($id) {
                use \Big\Hydrator\ObjectExtension\LoadableTrait;

                private $id;
                /**
                 * @BHY\PropertyCandidates(enabled=false, items={
                 *      @BHY\Property(enabled=false, conditionalRef="lazyLoadableCond", keyInRawData="first_name", dataSourceRef="nameSource", loading="EAGER", _keyInRawData=@BHY\Expression("second_name")),
                 *      @BHY\Property(_keyInRawData=@BHY\Expression(value="8"), dataSourceRef="nameSource", loading="EAGER")
                 * })
                 * @BHY\Property(_keyInRawData=@BHY\Expression(value="first_name"), dataSourceRef="nameSource", loading="EAGER")
                 */
                private $name;
                /**
                 * @BHY\Property(dataSourceRef="emailSource", loading="EAGER")
                 */
                private $email;

                public function __construct($id) { $this->id = $id; }
                public function getId() { return $this->id; }
                public function getName() { return $this->name; }
                public function getEmail() { return $this->email; }
            };

            $serviceProvider = function ($serviceKey) {
                switch ($serviceKey) {
                    case 'Big\HydratorTest\Fixture\PersonService':
                        return new Big\HydratorTest\Fixture\PersonService;
                    case 'Big\HydratorTest\Fixture\EmailService':
                        return new Big\HydratorTest\Fixture\EmailService;
                }
            };

            $hydrator = (new HydratorBuilder())
                ->addConfig([
                    'service_provider' => $serviceProvider,
                    /*'metadata_location' => [
                        'global' => [
                            'expression' => [
                                'keyword' => [
                                    'this_keyword' => '##this'
                                ]
                            ]
                        ]
                    ]*/
                ])
                ->build();

            $hydrator->hydrate($person);

            print_r('=======> BEGIN');
            print_r($person);
            print_r('=======> END');
        }
    }
}
