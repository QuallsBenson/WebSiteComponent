<?php namespace WebComponents\SiteBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICanBoogie\Inflector as Inflector;

/**
* Set's the default environment variables for current site
**/

interface SiteControllerInterface{

	/**
	* Stores data in the controller
	**/

	public function addSiteData( array $data, $override = true );

	/**
	* @return controller data
	**/

	public function getSiteData();


}