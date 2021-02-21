<?php
namespace Kassko\FrameworkBridge\Symfony\ObjectHydratorIntegrationTest;

use Kassko\FrameworkBridge\Symfony\ObjectHydrator\DependencyInjection\KasskoObjectHydratorExtension;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class HydratorTest extends TestCase
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
        $container = $this->createContainer();

        //$hydrator = $container->get('Kassko\ObjectHydrator\Hydrator');

        var_dump(get_class($this->hydrator));

        $this->assertTrue(true);
    }

	private function createContainer() : Container
    {
        $container = new ContainerBuilder();

        $extension = new KasskoObjectHydratorExtension;
        $extension->load([[]], $container);

        $container->compile();

        return $container;
    }
}
