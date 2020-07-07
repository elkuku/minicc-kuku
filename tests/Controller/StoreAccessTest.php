<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StoreAccessTest extends WebTestCase
{
    protected KernelBrowser $client;

    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
    }

    public function testStoreAccess(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stores');
        self::assertResponseRedirects();
    }
}
