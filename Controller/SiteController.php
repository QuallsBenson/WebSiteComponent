<?php namespace WebComponents\SiteBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICanBoogie\Inflector as Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
* Set's the default environment variables for current site
**/


class SiteController extends Controller implements SiteControllerInterface{


	protected $siteData   = [],
	          $bundleName = "";


    /**
    * Performs all initialization in the setContainer method
    **/


    public function setContainer( ContainerInterface $container = null )
    {

    	$ret = parent::setContainer( $container );
    	$this->init();
    	return $ret;

    }

    /**
    * @return Symfony\Component\DependencyInjection\ContainerInterface;
    **/

    public function getContainer()
    {

    	return $this->container;

    }


    /**
    * Calls all initialization functions
    * @return void
    **/


    public function init()
    {

    	$this->initSiteData();

    }


    /**
    * Set the default initialization variables
    * @return void    
    **/


	public function initSiteData()
	{

		//get site-wide global variables

		$globals = $this->getGlobals();

		$this->addSiteData( $globals );

		//get bundle specific Globals

		$bundleData = $this->getBundleGlobals();

		if( $bundleData )
		{

			$this->addSiteData( $bundleData );

		}

		//load and merge include data
		if( isset($bundleData['include']) ) 
		{

			$includeData = $this->getIncludeData( $bundleData["include"] );
			$this->addSiteData( $includeData );

		}




	}

	/**
	* @return array of sitewide global variables
	**/

	public function getGlobals()
	{

		return $this->container->getParameter( "web_components.globals" );

	}

	/**
	* @return array of globals for the bundle or false if undefined
	**/

	public function getBundleGlobals()
	{

		$bundle    = $this->getBundleName( true );
		$key       = $bundle.".globals";
		$container = $this->container; 


		if( $container->hasParameter( $key ) )
		{

			return $container->getParameter( $key );

		}

		return false;

	}

	/**
	* @return array of parameters
	**/

	public function getIncludeData( array $includes )
	{

		$data = [];
		$app  = $this->getContainer();

		foreach( $includes as $inc )
		{

			$data = array_merge( $data, $app->getParameter( $inc ) );

		}

		return $data;

	}


	/**
	* merge new site Data
	**/


	public function addSiteData( array $data, $override = true )
	{

		$param = [ $this->siteData, $data ];

		if( !$override ) array_reverse( $param );

		$this->siteData = call_user_func_array( "array_merge", $param );

		return $this;

	}


	/**
	* @return array of site data
	**/


	public function getSiteData()
	{

		return $this->siteData;

	}

	/**
	* @return array of debug data
	**/

	public function getDebugData()
	{
		$app     = $this->container;

		$devEnv  = in_array( $app->getParameter("kernel.environment"), ["dev", "test"] );

		if( $devEnv )
		{
			$default = [ 'debug' => true ];

			$key     = $this->getBundleName( true ).".globals.debug";
			$data    = $app->hasParameter( $key ) ? $app->getParameter( $key ) : [];

			return array_merge( $default, $data );

		}
		else 
		{
			return [ 'debug' => false ];
		}

	}


	/** 
	* Attempt to determine the current site bundle by the host 
	* @return string name of bundle
	**/


	public function resolveBundleName()
	{

		$data  = $this->getSiteData();
		$host  = $this->get("request")->getHost();

		if( isset($data[$host]) ) return $data[$host];

		return false;
	}


	/**
	* @return the name of the current bundle
	**/


	public function getBundleName( $underscore = false )
	{

		$bundle = $this->bundleName ?: $this->bundleName = $this->resolveBundleName();

		return $underscore && $bundle ? Inflector::get("en")->underscore( $bundle ) : $bundle;		

	}


	/**
	* merges siteData variables with the parameters array, then calls parent render
	**/


	public function render( $view, array $parameters = array(), Response $response = null)
	{

		$parameters = array_merge( $this->getSiteData(), $parameters );

		return parent::render( $view, $parameters, $response );

	}

} 


