<?php namespace WebComponents\SiteBundle\Elements\Image;


use WebComponents\SiteBundle\Elements\Image\ImageInterface;


class Image implements ImageInterface{


	use \WebComponents\SiteBundle\Elements\Image\ImageTrait;


	protected $source = array();


	public function __construct( array $ratios = array() )
	{

		foreach( $ratios as $ratio => $src )
		{

			$this->addSrc( $ratio, $src );

		}

	}


}