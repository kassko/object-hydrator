<?php
namespace Kassko\FrameworkBridge\Symfony\ObjectHydrator;

use Psr\Container\ContainerInterface;

class ServiceLocator
{
	private ContainerInterface $container;

	public function __construct(ContainerInterface $container)
	{
	 	$this->container = $container;
	}

	public function __invoke($key)
	{
		return $this->container->get($key);
	}
}
