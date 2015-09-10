<?php namespace WebComponents\SiteBundle\Post;


use WebComponents\SiteBundle\Content\ContentRepositoryInterface;
use Quallsbenson\WebComponents\Search\Interfaces\SearchableInterface;


class PostRepository implements ContentRepositoryInterface, SearchableInterface{


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

		return func_get_args();
	}


	public function viewContent( $slug, $category = null )
	{

		return func_get_args();

	}

	public function getContentId()
	{

		return 'post';

	}


}