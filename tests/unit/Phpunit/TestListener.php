<?php
namespace Kassko\ObjectHydratorUnitTest\Phpunit;

use PHPUnit\Framework\{TestListener as BaseTestListener, TestListenerDefaultImplementation, TestSuite, Test};

class TestListener implements BaseTestListener
{
    use TestListenerDefaultImplementation;

    public function startTestSuite(TestSuite $suite) : void
    {
        printf("\n\nTestSuite '%s' started.\n", $suite->getName());
    }

    public function endTestSuite(TestSuite $suite) : void
    {
        printf("\nTestSuite '%s' ended.\n\n", $suite->getName());
    }

    public function addSkippedTest(Test $test, \Throwable $e, float $time) : void
    {
        printf("Test '%s' has been skipped.\n\n", $test->getName());
    }
}
