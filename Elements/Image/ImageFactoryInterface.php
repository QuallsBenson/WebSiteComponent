<?php namespace WebComponents\SiteBundle\Elements\Image;


interface ImageFactoryInterface{

	/**
	* creates a new instance of the image, with given source
	* @return WebComponents\SiteBundle\Elements\Image\ImageInterface
	**/

	public function make( array $src );


}