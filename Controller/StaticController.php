<?php namespace WebComponents\SiteBundle\Controller;

use WebComponents\SiteBundle\Controller\SiteController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StaticController extends SiteController
{

    public function partialAction( $slug )
    {

        return $this->render('::Partials:partial.html.twig', [
                "partial" => $slug
        ]); 

    }

    public function templateAction( $slug )
    { 

        $data   = $this->getTemplateData( $slug );

        return $this->render("::Static:{$slug}.html.twig", $data );    

    }


    protected function getTemplateData( $slug )
    {

        $data = $this->getSiteData();

        return isset( $data["static_content"][$slug] ) ? $data["static_content"][$slug] : [];

    }


}
