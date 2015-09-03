<?php namespace WebComponents\SiteBundle\Elements\Image;


class ImageFactory{

	/**
	* creates a new instance of the image, with given sourceInterface
	**/

	public function make( array $src )
	{

		return new Image( $src );

	}


}