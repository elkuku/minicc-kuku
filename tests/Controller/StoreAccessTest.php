<?php

namespace App\Tests\Controller;

use App\Tests\FixtureAwareTestCase;
use App\Tests\Fixtures\StoreFixture;

class StoreAccessTest  extends FixtureAwareTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
        $kernel = static::bootKernel();

        // $this->addFixture(new StoreFixture());
        //
        // $this->executeFixtures();
    }

        public function testSomething()
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        // $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    public function testStoreAccess()
    {
        // $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();

        $this->client->request('GET', '/stores');
        $this->assertResponseRedirects();
        // $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

}
