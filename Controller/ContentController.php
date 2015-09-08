<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;
use WebComponents\SiteBundle\Content\UndefinedContentTypeException;
use WebComponents\SiteBundle\Content\ContentRepositoryInterface;

// List Routes:
//----------------------------
// content
// content/category
// content/category/page

// View Routes
//----------------------------
// content/category/slug
// content/view/slug
// content

// Search Routes
//----------------------------
// content/search/?keywords=term?category
// search/?content[]=1&content[]=2


class ContentController extends SiteController
{

	protected $CONTENT_METHODS = [ 'list', 'view' ];


	public function resolveAction( $path )
	{

		//strip trailing slash from path if it has one
		if( $path[ strlen( $path ) - 1 ] === "/"  ) 
		{
			$path = substr( $path, 0, strlen( $path )-1 );
		}

		//split path into parts
		$path      = explode("/", $path );


		//if empty variables in array, request was malformed
		if( in_array( '', $path ) ) throw $this->createNotFoundException();


		$content   = @$path[0];

		$paginated = ( (int) end( $path ) ) > 0; 
		$page      = null;

		$count     = count( $path );

		//add the default contentType to the beginning of the path parts

		if( ($paginated && $count == 1) || $count === 0 )
		{

			$path    = array_merge( [ $this->getDefaultContentType() ], array_values( $path ) );
			$count   = count( $path );
		    $content = @$path[0];

		}

		//if path is paginated ex:
		//      /content/category/page
		//      /content/page
		//      /content

		//perform list action on content-type

		if( $paginated && ( $count == 2 || $count === 3 ) || ( !$paginated && $count === 1 ) )
		{

			$action   = 'list';
			$page     = (int) end( $path );

			$category = ( $count <= 2 ) ? 'all' : $path[1];

		}

		//if path containts 2 parts, use list action

		else if( $count == 2 )
		{

			$action   = 'list';
			$category = end( $path );

		}

		//else if it contains 3 use view action
			//  content/category/slug
		    //  content/view/slug

		else if( $count === 3 )
		{

			$action   = 'view';
			$slug     = end( $path );
			$category = $path[1];

			if( !in_array( $path[1], [ 'view', 'read', 'show' ] ) )
			{

				$category = null;

			}

		}

		//finally, if not matched, requested is malformed
		//throw not found exception

		else
		{

			throw $this->createNotFoundException();

		}


		if( $action === 'list' )
		{

			var_dump( $path, 'list' );

			//get data from repository and push to siteData array
			$repo = $this->listAction( $content, $category, $page );

		}
		else
		{

			var_dump( $path, 'view' );

			//get data from repository and push to siteData array
			$repo = $this->viewAction( $slug, $content, $category );

		}

		//load any linked/default content
		$queryInfo = [ 
			'category' => $category, 
            'page'     => $page, 
            'slug'     => @$slug 
		];


		$this->addLinkedData( $repo, $queryInfo );


		return $this->createResponse( $content.":".$action, $queryInfo, 200 );


	}



	/**
	* lists rows from database
	**/

	public function listAction( $contentType, $category, $page = null )
	{

		$page = (int) ( $page ?: $page );


		$repo = $this->getRepository( $contentType );


		$content = $repo->listContent( $category, $page );


		//make content avaiable to the view
		$this->siteData[ 'content' ][ $contentType ] = $content;


		//return the repository
		return $repo;

	}

	/**
	* display single record from database
	**/

	public function viewAction( $slug, $contentType, $category = null )
	{

		$repo    = $this->getRepository( $contentType );

		//load the view content by slug
		$content = $repo->viewContent( $slug, $category );

		//send to view
		$this->siteData[ 'content' ][ $contentType ] = $content;

		//return the repository
		return $repo;

	}

	public function getRepository( $contentType )
	{

		$data    = $this->getSiteData();
		$config  = @$data['content_types'][ $contentType ];

		if( !$config )
		{
			throw UndefinedContentTypeException("Content-type: '" .$contentType ."'' is not defined in global data" );
		}


		//TODO: put this code in try catch block, as it may fail
		//to give user more detailed fail message

		$repo = $this->get( $config['repository'] );

		//set configuration
		$repo->setContentConfig( $config );


		return $repo;

	}


	public function getDefaultContentType()
	{

		$data = $this->getSiteData();
		return @$data['content_types']['default'];

	}

	public function getLinkedContent( ContentRepositoryInterface $content, $data )
	{

		return [];

	}

	public function createResponse( $action, $data, $code )
	{

		var_dump( $this->siteData['content'] ); exit;

		return null;

	}


}
