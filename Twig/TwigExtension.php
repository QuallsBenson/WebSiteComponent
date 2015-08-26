<?php namespace WebComponents\SiteBundle\Twig;


use Symfony\Component\Routing\Router;


class TwigExtension extends \Twig_Extension{


	public function setRouteService( Router $router )
	{

		$this->router = $router;

	}

	public function getRouteService()
	{

		return $this->router;

	}


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('url', array($this, 'urlFunction')),
        );
    }

    /**
    * Overload the url function to allow url function to be called with array parameters or string values
    * 
    **/

    public function urlFunction( $args )
    {

    	$args = func_get_args();

    	//if only one argument is passed, and that argument is an array
    	//call the route generator with that argument

    	if( count( $args ) === 1 && @is_array( $args[0] ) )
    	{

    		$args = $args[0];

    	}

    	//if only one arg was passed, and that arg is a valid url
    	//return the string as is

    	if( count( $args ) === 1 &&  @filter_var( $args[0] , FILTER_VALIDATE_URL) ){

    		return $args[0];

    	}

    	return call_user_func_array( [ $this->getRouteService(), "generate" ] , $args );

    }


    public function getName()
    {
        return 'wc_sitebundle_extension';
    }

}