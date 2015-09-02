<?php namespace WebComponents\SiteBundle\Controller;

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