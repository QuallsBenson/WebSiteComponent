<?php namespace WebComponents\SiteBundle\Elements\Image;


trait ImageTrait{

	/**
	* @return the first image source or null if none set 
	**/

	public function __toString()
	{

		$src = array_values( $this->source );
		return @$src[0];

	}

	/**
	* add a source to the image for ratio
	* @return WebComponents\SiteBundle\Elements\ImageTrait
	**/

	public function addSrc( $ratio, $src )
	{

		$this->source[ $ratio ] = $src;
		return $this;

	}

	/**
	* @return the source of the image for the given ratio or null if not set 
	**/

	public function src( $ratio = "1x" )
	{

		return $this->source[ $ratio ];

	}

	/**
	* @return bool, true if has ratio false if not
	**/

	public function hasRatio( $ratio )
	{

		return isset( $this->source[ $ratio ] );

	}	


}