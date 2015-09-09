<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;
use WebComponents\SiteBundle\Content\UndefinedContentTypeException;
use WebComponents\SiteBundle\Content\ContentRepositoryInterface;
use WebComponents\SiteBundle\Content\ContentUnavailableException;
use WebComponents\SiteBundle\Content\ContentConfigException;
use Symfony\Component\HttpFoundation\JsonResponse;

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

	static protected $CONTENT_METHODS = [ 'list', 'view' ];

/*
	public function initSiteData()
	{

		$content = $this->getRequestParam( 'with' );


		if( !empty( $content ) )
		{
			
			foreach( $content as $c )
			{

				if( str )

			}
			//$this->siteData['content']
		}


		return parent::initSiteData();

	}
*/	


	public function resolveAction( $path )
	{

		//strip trailing slash from path if it has one
		if( $path[ strlen( $path ) - 1 ] === "/"  ) 
		{
			$path = substr( $path, 0, strlen( $path )-1 );
		}

		//split path into parts
		$path      = explode("/", $path );


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

		//if empty variables in array, request was malformed
		if( in_array( '', $path ) ) throw $this->createNotFoundException();

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

		//else if it contains only one, and a default
		//action is set for the content, then fire that
		//action using the content type name as the slug/category

		else
		{

			$repoConfig = $this->getRepository( $content )->getContentConfig();

			//if a valid action is set, set that action

			$defaults = @$repoConfig['defaults'];


			if( in_array( $defaults['action'], self::$CONTENT_METHODS )  )
			{

				$slug     = @$defaults['slug']     ?: $content;
				$category = @$defaults['category'] ?: $content;  
				$action   = $defaults['action']; 

			}

			//else give a not found exception

			else
			{

				throw $this->createNotFoundException();

			}

		}


		if( $action === 'list' )
		{

			//get data from repository and push to siteData array
			$repo = $this->listAction( $content, $category, $page );

		}
		else
		{

			//get data from repository and push to siteData array
			$repo = $this->viewAction( $slug, $content, $category );

		}

		$config = $repo->getContentConfig();

		//load any linked/default content
		$queryInfo = [ 
			'category' => $category, 
            'page'     => $page, 
            'slug'     => @$slug,
            'action'   => $action,
            'template' => @$config['templates'][ $action ] 
		];


		$this->addLinkedData( $repo, $queryInfo );


		return $this->createResponse( $repo, $queryInfo, 200 );


	}



	/**
	* lists rows from database
	**/

	public function listAction( $contentType, $category, $page = null )
	{

		$page = (int) ( $page ?: $page );


		$repo = $this->getRepository( $contentType );


		$content = $repo->listContent( $category, $page );

		//todo throw an error if no content set


		//make content avaiable to the view
		$this->siteData[ 'content' ][ $repo->getContentId() ] = $content;


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
		$this->siteData[ 'content' ][ $repo->getContentId() ] = $content;

		//return the repository
		return $repo;

	}

	public function getRepository( $contentType )
	{

		$data    = $this->getSiteData();
		$config  = @$data['content_types'][ $contentType ];

		if( !$config )
		{
			throw new UndefinedContentTypeException("Content-type: '" .$contentType ."'' is not defined in global data" );
		}


		//TODO: put this code in try catch block, as it may fail
		//to give user more detailed fail message

		$repo = $this->get( $config['repository'] );

		//set configuration
		$repo->setContentConfig( $config );


		return $repo;

	}

	public function getContentData( $content, array $param = array() )
	{


		if( is_string( $content ) )
		{

			//if $content is formatted 'type:action', get the action
			if( strpos( $content, ":") !== false )
			{

				$action = explode( ":", $content )[1];

				if(!in_array( $action, self::$CONTENT_METHODS ))
				{

					throw new \InvalidArgumentException("Cannot call action '".$action."' on Content-Type, only content" );

				}

			}

		}

	}


	public function getDefaultContentType()
	{

		$data = $this->getSiteData();
		return @$data['content_types']['default'];

	}

	public function addLinkedData( ContentRepositoryInterface $content, $data )
	{

		return [];

	}

	public function createResponse( ContentRepositoryInterface $repo, $data, $code = 200 )
	{

		$config  = $repo->getContentConfig();
		$ajax    = $this->get('request')->isXmlHttpRequest(); 

		//if content_type is viewless or
		//if an ajax request is being made, return 
		//info as json

		if( @$config['viewless'] === true || $ajax )
		{

			//if ajax request is being made, and ajax is set
			//but set to bool false, throw access denied error

			if( $ajax && ( @$config['ajax'] === false ) )	
			{

				throw new ContentUnavailableException("Content-Type: '". $repo->getContentId() ."' is not available via ajax request");

			}

			//return a json response with only content, and data passed
			//don't return all site data, as it may be sensitive
			$param = [ 'content' => $this->siteData['content'] ];
			$param = array_merge( $param, $data );


			return new JsonResponse( $param, $code );		

		}

		if( !$data['template'] )
		{

			throw new ContentConfigException("No '".$data['action'] ."' Template is configured for Content-Type: '".$repo->getContentId() );

		}

		//do a default render with the template

		return $this->render( $data['template'], $data );

	}


}
