<?php

namespace Big\HydratorTest;

use Big\Hydrator\{ClassMetadata as BHY, DataFetcher, Hydrator, HydratorBuilder};
use Big\StandardClassMetadata as BSTD;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class HydratorTest extends TestCase
{
    /**
     * @test
     */
    public function basic()
    {
        $ids = [1, 2];

        foreach ($ids as $id) {
            /**
             * @BHY\DataSources({
             *      @BHY\DataSource(
             *          id="nameSource",
             *          method=@BSTD\Method(class="Big\HydratorTest\Fixture\PersonService", name="getData", args={"#id"}),
             *          indexedByPropertiesKeys=true
             *      ),
             *      @BHY\DataSource(
             *          id="emailSource",
             *          method=@BSTD\Method(class="Big\HydratorTest\Fixture\EmailService", name="getData", args={"#id"})
             *      )
             * })
             *
             *
             * @BHY\Conditionals({
             *      @BHY\Conditional\Expression(
             *          id="lazyLoadableCond",
             *          expression="expr(object.getId() == 1)",
             *      ),
             *      @BHY\Conditional\Method(
             *          id="privateEmailCond",
             *          method=@BSTD\Method(
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
                 * @BHY\CandidateProperties({
                 *      @BHY\Property(conditionalRef="lazyLoadableCond", keyInRawData="first_name", dataSourceRef="nameSource", lazyLoaded=false)
                 *      @BHY\Property(keyInRawData="first_name", dataSourceRef="nameSource", lazyLoaded=true)
                 * })
                 */
                private $name;
                /**
                 * @BHY\Property(dataSourceRef="emailSource", lazyLoaded=false)
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
