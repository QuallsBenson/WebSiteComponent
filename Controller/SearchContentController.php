<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;
use Quallsbenson\WebComponents\Search\Interfaces\SearchProviderInterface;
use Quallsbenson\WebComponents\Search\Interfaces\SearchResultProviderInterface;
use Symfony\Component\HttpFoundation\Request;

// Search Routes
//----------------------------
// search/content?keywords=term?category
// search/?content[]=1&content[]=2


class SearchContentController extends ContentController
{

	public function searchAction( Request $request, $content = null )
	{

		$this->request = $request;

		//if no content type given search all searchable content from query
		$query    = $request->query;


		$content  = $content ? (array) $content : $query->get("content");
		$content  = $content ?: $this->getDefaultSearchContent();


		$keywords = $query->get("keywords"); 

		$config = $this->getSearchConfig();

		foreach( $config["with"] as $with )
		{

			$this->evalContentExpression( $with );

		}


		//if no keywords/content given, show search landing page
		if( !$keywords || !$content )
		{
			return $this->indexAction();
		}


		//perform search
		$results  = $this->searchContent( $keywords, $content )->results();

		//sort results by relevence
		//$results->order();


		//if filters are defined, filter results
		if( $filters = $query->get("filters") )
		{

			$results = $this->filterResults( $filters, $results );

		}

		//otherwise just use all results		
		else
		{

			$results = $results->all();

		}
		

		//send search results to sitedata
		$this->siteData['content']['search'] = [
				'keywords'     => $keywords,
				'filters'      => $filters,
				'results'      => $results,
				'contentTypes' => $content
		];


		return $this->createResponse( $config, 'search', [
						'action'   => 'results',
						'template' => $config['templates']['results']
			   ] );
		

	}

	public function indexAction()
	{

		$config = $this->getSearchConfig();


		return $this->createResponse( $config, 'search', [
						'action'   => 'index',
						'template' => $config['templates']['index']
			]);

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
		

		return $this->getSearchProvider()->search( $keywords, $repositories );

	}


	public function filterResults( array $filters, SearchResultProviderInterface $results )
	{


		return $results->filter( $filters, 1 )->allFiltered();


	}

	public function getSearchProvider()
	{

		$searchServiceId = $this->siteData['service.search_provider'];

		$provider = $this->get( $searchServiceId );

		if( !$provider instanceof SearchProviderInterface )
		{

			throw new \InvalidArgumentException(" Search Provider: '" .$searchServiceId ."', must implement Quallsbenson\WebComponents\Search\Interfaces\SearchProviderInterface ");

		}

		return $provider;

	}


	public function getSearchConfig()
	{

		return $this->siteData['config.search'];

	}

	public function getDefaultSearchContent()
	{

		return $this->getSearchConfig()['defaults']['content'];

	}


}