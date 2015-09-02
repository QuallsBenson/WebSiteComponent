<?php namespace WebComponents\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//removes trailing slash from url
//http://symfony.com/doc/current/cookbook/routing/redirect_trailing_slash.html

class RedirectController extends Controller
{

    public function removeTrailingSlashAction(Request $request)
    {

        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);

    }


}
