<?php namespace WebComponents\SiteBundle\Content;


interface ContentRepositoryInterface{


	public function setContentConfig( array $options );


	public function getContentConfig();


	public function listContent( $category = null, $page = null );


	public function viewContent( $slug, $category = null );


}