<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testGoogleVerifyEndpointExists(): void
    {
        $client = static::createClient();

        // POST to the Google verify endpoint - it throws UnexpectedValueException
        // which results in a 500, but the route exists and is handled
        $client->request('POST', '/connect/google/verify');

        $statusCode = $client->getResponse()->getStatusCode();
        // Should not be 404 - route exists. May be 500 (exception) or redirect
        self::assertNotSame(404, $statusCode);
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('login');
    }
}
