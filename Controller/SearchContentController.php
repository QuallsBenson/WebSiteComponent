<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;


// Search Routes
//----------------------------
// search/content?keywords=term?category
// search/?content[]=1&content[]=2


class SearchContentController extends ContentController
{

	public function searchAction( $content = null, Request $request )
	{

		//if no content type given search all searchable content from query
		$query    = $request->query;

		$content  = $content ? (array) $content : $query->get("content");
		$keywords = $query->get("keywords"); 

		//perform search
		$search  = $this->searchContent( $keywords, $content );
		

	}

	public function searchContent( $keywords, array $content )
	{

		//get the actual repositories assigned to the
		//content name

		$repositories = [];

		foreach( $content as $r )
		{

			$repositories[] = $this->getRepository( $r );

		}

		//return search

		return $this->get("website.search_provider")->search( $keywords, $repositories );

	}


}