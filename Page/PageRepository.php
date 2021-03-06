<?php namespace WebComponents\SiteBundle\Page;


use WebComponents\SiteBundle\Content\ContentRepositoryInterface;
use Quallsbenson\WebComponents\Search\Interfaces\SearchableInterface;


class PageRepository implements ContentRepositoryInterface, SearchableInterface{


	protected $config = [];


	public function search( $keywords )
	{

		return [];

	}


	public function searchId()
	{

		return $this->getContentId();

	}



	public function setContentConfig( array $options )
	{

		$this->config = $options;

	}


	public function getContentConfig()
	{

		return $this->config;

	}



	public function listContent( $category = null, $page = null )
	{

		return explode(" ", "an array of rows from some database" );

	}


	public function viewContent( $slug, $category = null )
	{

		return explode(" ", "A Single Row From a Database");

	}

	public function getContentId()
	{

		return 'page';

	}


}