<?php namespace WebComponents\SiteBundle\Content;


interface ContentRepositoryInterface{


	public function listContent( $category = null, $page = null );


	public function viewContent( $slug, $category = null );


}