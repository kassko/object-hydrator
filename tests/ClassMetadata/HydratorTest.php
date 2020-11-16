<?php

namespace Big\HydratorTest\ClassMetadata;

use Big\Hydrator\ClassMetadata as BHY;
use PHPUnit\Framework\TestCase;

class HydratorTest extends TestCase
{
    /**
     * test
     */
    public function basic()
    {
        $person = new class(1) {
            private $id;
            /**
             * @BHY\Hydrator(name="foo", dataSourceRef="nameSource")
             */
            private $name;
            /**
             * @BHY\Hydrator(dataSourceRef="emailSource")
             */
            private $email;

            public function __construct($id) { $this->id = $id; }
            public function getId() { return $this->id; }
            public function getName() { return $this->name; }
            public function getEmail() { return $this->email; }
        };

        $reflClass = new \ReflectionClass($person);
        $reflProp = $reflClass->getProperty('name');

        $reader = new \Doctrine\Common\Annotations\AnnotationReader;
        $annotations = $reader->getPropertyAnnotations($reflProp);

        //var_dump($annotations);
    }
}
