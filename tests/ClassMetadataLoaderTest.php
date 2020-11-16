<?php

namespace Big\HydratorTest;

use Big\SourceCombinator\ClassMetadata\{DataSource, DataSourceCollection};
use Big\Hydrator\ClassMetadata\{ExcludedProperty, Hydrator, PropertiesExcludedByDefault};
use Big\StandardClassMetadata\Method;
use PHPUnit\Framework\TestCase;

class ClassMetadataLoaderTest extends TestCase
{
    /**
     * test
     */
    public function basic()
    {
        /**
         * @DataSourceCollection({
         *      @DataSource(
         *          id="nameSource",
         *          method={@Method(class="Kassko\Example\PersonService", function="getData", args="#id")}
         *      ),
         *      @DataSource(
         *          id="emailSource",
         *          method={@Method(class="Kassko\Example\EmailService", function="getData", args="#id")}
         *      )
         * })
         */
        $person = new class(1) {
            private string $id;
            /**
             * @Hydrator(name="foo", dataSourceRef="nameSource")
             */
            private string $name;
            /**
             * @Hydrator(dataSourceRef="emailSource")
             */
            private string $email;

            public function __construct($id) { $this->id = $id; }
            public function getId() { return $this->id; }
            public function getName() { return $this->name; }
            public function getEmail() { return $this->email; }
        };

        $loader = new \Big\Hydrator\ClassMetadataLoader(
            (new \Big\Hydrator\LoaderResolver)->addLoader(
                new \Big\Hydrator\ClassMetadataLoader\DoctrineAnnotationLoader(
                    new \Doctrine\Common\Annotations\AnnotationReader
                )
            )
        );

        $classMetadata = $loader->loadMetadata($person);
    }
}
