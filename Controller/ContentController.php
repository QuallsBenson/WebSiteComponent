<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;




class ContentController extends SiteController
{

	protected $CONTENT_METHODS = [ 'list', 'view' ];


	public function resolveAction( $path )
	{
 
		$path      = explode("/", $path);
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

		//perform list action on content-type

		if( $paginated && ( $count == 2 || $count === 3 ) )
		{

			$action   = 'list';
			$page     = (int) end( $path );

			$category = ( $count === 2 ) ? 'all' : $path[1];

		}

		//if path containts 2 parts, use list action

		else if( $count == 2 )
		{

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

			$data = $this->listAction( $content, $category, $page );

		}
		else
		{

			$data = $this->viewAction( $slug, $content, $category );

		}

		//load any linked/default content
		$linkedData = $this->getlinkedContent( $content, $action, [
									 'data'     => $data,
									 'category' => $category,
									 'page'     => $page,
									 'slug'     => @$slug  
								]);


		return $this->createResponse( $content.":".$action, array_merge( $data, $linkedData ), 200 );


	}



	/**
	* lists rows from database
	**/

	public function listAction( $contentType, $category, $page = null )
	{

		$page = (int) ( $page ?: $page );
		$repo = $this->getRepository( $contentType );



	}

	/**
	* display single record from database
	**/

	public function viewAction( $contentType, $category = null )
	{



	}

	public function getRepository( $contentType )
	{




	}

	public function getContentRepositoryService( $alias )
	{

		return $alias;

	}


	public function getDefaultContentType()
	{

		return 'type';

	}

	public function getLinkedContent( $content, $action, $data )
	{

		return [];

	}

	public function createResponse( $action,  )
	{

		//return 

	}


}
