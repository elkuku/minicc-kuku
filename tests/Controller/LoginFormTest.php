<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginFormTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Login');
        self::assertSelectorExists('form[action="/login"]');
        self::assertSelectorExists('input[name="identifier"]');
        self::assertSelectorExists('input[name="_csrf_token"]');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testSuccessfulLoginRedirectsToWelcome(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'admin@example.com',
        ]);

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertRouteSame('welcome');
    }

    public function testSuccessfulLoginSetsRememberMeCookie(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'user1@example.com',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $cookie = $client->getCookieJar()->get('KUKUREMEMBERME');
        $this->assertInstanceOf(Cookie::class, $cookie, 'Remember me cookie should be set');
    }

    public function testLoginWithInvalidUserShowsError(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'nonexistent@example.com',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/login');
        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
    }

    public function testLoginWithEmptyIdentifierShowsError(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => '',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/login');
        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
    }

    public function testLoginWithInvalidCsrfTokenFails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        // Submit form with invalid CSRF token
        $client->request('POST', '/login', [
            'identifier' => 'admin@example.com',
            '_csrf_token' => 'invalid_token',
        ]);

        self::assertResponseRedirects('/login');
        $client->followRedirect();

        self::assertSelectorExists('.alert-danger');
    }

    public function testLoggedInUserCanAccessProtectedRoute(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'admin@example.com',
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Now try to access an admin-protected route
        $client->request('GET', '/admin/payments');

        self::assertResponseIsSuccessful();
    }

    public function testNonAdminCannotAccessAdminRoute(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'user1@example.com',
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Try to access an admin-protected route
        $client->request('GET', '/admin/payments');

        self::assertResponseStatusCodeSame(403);
    }

    public function testLogoutWorks(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'admin@example.com',
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Verify we're logged in
        self::assertResponseIsSuccessful();

        // Logout
        $client->request('GET', '/logout');

        self::assertResponseRedirects('/');
        $client->followRedirect();

        // Try to access a protected route - should redirect to login
        $client->request('GET', '/admin/payments');

        self::assertResponseRedirects();
        $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testLoginRedirectsToOriginallyRequestedPage(): void
    {
        $client = static::createClient();

        // Try to access a protected page first
        $client->request('GET', '/admin/payments');

        // Should redirect to login
        self::assertResponseRedirects();
        $client->followRedirect();

        // Now login
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Login')->form([
            'identifier' => 'admin@example.com',
        ]);

        $client->submit($form);

        // Should redirect back to the originally requested page
        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertRouteSame('admin_payments');
    }
}
