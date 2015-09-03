<?php namespace WebComponents\SiteBundle\Elements\Image;


interface ImageInterface{

	/**
	* @return the source of the image for the given ratio or null if not set 
	**/

	public function src( $ratio = "1x" );

	/**
	* add a source to the image
	**/

	public function addSrc( $ratio, $src );

	/**
	* @return bool, true if has ratio false if not
	**/

	public function hasRatio( $ratio );


}