<?php

namespace App\Tests\Controller;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        if (!$client->getResponse()->isSuccessful()) {
            $c = $client->getResponse()->getContent();

            file_put_contents('gugu.html', $c);
        }

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider(): ?Generator
    {
        yield ['/'];
        yield ['/about'];
        yield ['/contact'];
        yield ['/login'];
    }
}
