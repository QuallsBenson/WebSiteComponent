<?php

namespace WebComponents\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StaticControllerTest extends WebTestCase
{
    public function testPartial()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/static/partial');
    }

    public function testTemplate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/static/{slug}');
    }

}
