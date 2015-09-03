<?php namespace WebComponents\SiteBundle\App;


use Symfony\Component\DependencyInjection\ContainerInterface;


trait AppTrait{


	public function setContainer(ContainerInterface $container)
	{

		$this->container = $container;

	}

	public function app()
	{

		return $this->container;

	}

}