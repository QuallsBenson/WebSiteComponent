<?php namespace WebComponents\SiteBundle\Twig;


use Symfony\Component\Routing\Router;
use Twig_Environment;
use WebComponents\SiteBundle\Elements\Image\ImageInterface;
use WebComponents\SiteBundle\Elements\Image\Image;
use WebComponents\SiteBundle\Elements\Image\ImageFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class TwigExtension extends \Twig_Extension implements ContainerAwareInterface{


    use \WebComponents\SiteBundle\App\AppTrait;


    public function setRouteService( Router $router )
    {

        $this->router = $router;

    }


    public function getRouteService()
    {

        return $this->router;

    }


    public function setImageFactory( ImageFactory $image )
    {

        $this->imageFactory = $image;

    }


    public function getImageFactory()
    {

        return $this->imageFactory;

    }


    public function getFunctions()
    {

        return array(
            new \Twig_SimpleFunction('url', array($this, 'urlFunction')),
            new \Twig_SimpleFunction('retina', array($this, 'retinaFunction')),
            new \Twig_SimpleFunction('retina_background', array($this, 'retinaFunction')),
        );

    }

    public function getFilters()
    {

        return array(
            new \Twig_SimpleFilter('Image', array($this, 'imageFilter')),
            new \Twig_SimpleFilter('slash', 'addslashes'),
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

        if( count( $args ) === 1 && ( @filter_var( $args[0] , FILTER_VALIDATE_URL) || $args[0] === "#" )  ){

            return $args[0];

        }

        return call_user_func_array( [ $this->getRouteService(), "generate" ] , $args );

    }

    /**
    *  creates markup for a retina image 
    *  @return markup for retina image
    **/

    public function retinaFunction( ImageInterface $image, $ratio = '1x', $ratio2 = '2x' )
    {

        $markup = '';

        $ratios = func_get_args();

        //remove image from ratios array
        unset( $ratios[0] );

        //merge default values with user input
        $ratios = array_merge( [ $ratio, $ratio2 ], array_values( $ratios ) );

        //get the asset helper to correct urls
        $asset  = $this->app()->get("templating.helper.assets");


        for( $i = 0; isset($ratios[$i]); $i++ )
        {

            $ratio   = $ratios[$i];

            //skip to the next if ratio is not available
            if( !$image->hasRatio( $ratio ) ) continue;


            $src     = $asset->getUrl( $image->src( $ratio ) );


            $markup .= "data-{$ratio}={$src} "; // data-1x="path/to/image"

        }


        return $markup;

    }

    /**
    *   @return WebComponents\SiteBundle\Elements\Image\Image
    **/

    public function imageFilter( $image )
    {

        //if already image object return 
        if( $image instanceof ImageInterface ) return $image;


        $factory = $this->getImageFactory();

        //if the image is an array, pass it the the image factory
        //as is, otherwise convert to array with default ratio

        if( !is_array( $image ) )
        {
            $image = [
                '1x' => $image
            ];
        }

        return $factory->make( $image );

    }


    public function getName()
    {
        return 'wc_sitebundle_extension';
    }

}