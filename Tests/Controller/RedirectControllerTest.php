<?php

namespace WebComponents\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RedirectControllerTest extends WebTestCase
{
    public function testRemovetrailingslash()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/{path}');
    }

}
