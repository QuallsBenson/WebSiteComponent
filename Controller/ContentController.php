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

		if( $path )
		{

			//strip trailing slash from path if it has one
			if( @$path[ strlen( $path ) - 1 ] === "/"  ) 
			{
				$path = substr( $path, 0, strlen( $path )-1 );
			}

			//split path into parts
			$path      = explode("/", $path );

			$content   = @$path[0];

		}
		else 
		{

			$path = [];

		}
		

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


		//if it's a search forward to the search controller
		if( ($count === 1 || $count === 2) && $content === 'search' )
		{

			return $this->forward("WebsiteBundle:SearchContent:search", [
				    'request' => $this->get('request')
			]);
 
		}


		//if path is paginated ex:
		//      /content/category/page
		//      /content/page

		//perform list action on content-type

		if( $paginated && ( $count == 2 || $count === 3 ) )
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

			//if a valid config action is given, set as action

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
			$repo = $this->viewAction( $content, $slug, $category );

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


		$this->addLinkedContent( $repo, $queryInfo );

		/*
		echo "<pre>";

		var_dump( $this->siteData['content'] ); exit;
		*/

		return $this->createResponse( $repo->getContentConfig(), $repo->getContentId(), $queryInfo, 200 );


	}


	/**
	* lists rows from database
	**/

	public function listAction( $contentType, $category = null, $page = null )
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

	public function viewAction( $contentType, $slug, $category = null )
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

		//if the content-type is already a repository
		//just return it

		if( $contentType instanceof ContentRepositoryInterface )
		{
			return $contentType;
		}

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


	public function evalContentExpression( $content )
	{

		list( $repo, $param ) = $this->parseContentExpression( $content );

		$param = array_merge( [$repo[0]], $param );

		return call_user_func_array( [ $this, $repo[1] ], $param );

	}


	public function parseContentExpression( $content )
	{

		//parameters to be called with content callable
		$param = [];

		//if content is array, the second element is treated like parameters
		if( is_array( $content ) ){

			list( $content, $param ) = $content;

			//make params an array if it's not already
			if( !is_array( $param ) ) $param = (array) $param;

		}

		//cast content expression to string
		$content = (string) $content;


		//if $content is formatted 'type:action', get the action
		if( strpos( $content, ":") !== false )
		{

			list($content, $action) = explode( ":", $content );


			if(!in_array( $action, self::$CONTENT_METHODS ))
			{

				throw new \InvalidArgumentException("Cannot call undefined action '".$action."' on Content-Type" );

			}

			$repo  = $this->getRepository( $content );


			return [ [ $repo, $action.'Action' ], $param ];

		}

		//else give invalid config exception

		else{


			throw new ContentConfigException(" Invalid format '".$content."' given for Content-Type expression, must be contentType:action");
		
		}


	}


	public function getDefaultContentType()
	{

		$data = $this->getSiteData();
		return @$data['content_types']['default'];

	}


	public function addLinkedContent( ContentRepositoryInterface $content )
	{

		$config = $content->getContentConfig();

		if( isset( $config['with'] ) )
		{

			foreach( $config['with'] as $expression )
			{

				$this->evalContentExpression( $expression );

			}

		}

	}


	public function createResponse( array $config, $contentType, array $data = array(), $code = 200 )
	{

		$request = isset( $this->request ) ? $this->request : $this->get('request');

		$ajax    = $request->isXmlHttpRequest();
		$format  = $request->query->get("format"); 

		//if content_type is viewless or
		//if an ajax request is being made, return 
		//info as json

		if( ( @$config['viewless'] === true || $ajax ) && strtolower( trim($format) ) !== 'text/html' )
		{

			//if ajax request is being made, and ajax is set
			//but set to bool false, throw access denied error

			if( $ajax && ( @$config['ajax'] === false ) )	
			{

				throw new ContentUnavailableException("Content-Type: '". $contentType ."' is not available via ajax request");

			}

			//return a json response with only content, and data passed
			//don't return all site data, as it may be sensitive
			$param = [ 'content' => $this->siteData['content'] ];
			$param = array_merge( $param, $data );


			return new JsonResponse( $param, $code );		

		}

		if( !$data['template'] )
		{

			throw new ContentConfigException("No '".$data['action'] ."' Template is configured for Content-Type: '".$contentType );

		}

		//do a default render with the template

		return $this->render( $data['template'], $data );

	}


}
