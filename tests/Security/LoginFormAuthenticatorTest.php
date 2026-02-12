<?php

declare(strict_types=1);

namespace App\Tests\Security;

use ReflectionMethod;
use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use UnexpectedValueException;

final class LoginFormAuthenticatorTest extends TestCase
{
    private function createRouter(): RouterInterface
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')
            ->willReturnCallback(fn(string $route): string => '/' . $route);

        return $router;
    }

    public function testSupportsReturnsTrueForPostLogin(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $request = Request::create('/login', 'POST');

        $this->assertTrue($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseForGetLogin(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $request = Request::create('/login', 'GET');

        $this->assertFalse($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseForOtherPaths(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $request = Request::create('/other', 'POST');

        $this->assertFalse($authenticator->supports($request));
    }

    public function testAuthenticateThrowsExceptionInProductionEnv(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'prod');
        $session = $this->createStub(SessionInterface::class);
        $request = Request::create('/login', 'POST', [
            'identifier' => 'user@example.com',
            '_csrf_token' => 'token123',
        ]);
        $request->setSession($session);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('GTFO!');

        $authenticator->authenticate($request);
    }

    public function testAuthenticateThrowsExceptionForEmptyIdentifier(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $session = $this->createStub(SessionInterface::class);
        $request = Request::create('/login', 'POST', [
            'identifier' => '',
            '_csrf_token' => 'token123',
        ]);
        $request->setSession($session);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User identifier cannot be empty.');

        $authenticator->authenticate($request);
    }

    public function testAuthenticateReturnsPassportInTestEnv(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $session = $this->createStub(SessionInterface::class);
        $request = Request::create('/login', 'POST', [
            'identifier' => 'user@example.com',
            '_csrf_token' => 'token123',
        ]);
        $request->setSession($session);

        $passport = $authenticator->authenticate($request);

        $this->assertSame('user@example.com', $passport->getBadge(UserBadge::class)?->getUserIdentifier());
    }

    public function testAuthenticateWorksInDevEnv(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'dev');
        $session = $this->createStub(SessionInterface::class);
        $request = Request::create('/login', 'POST', [
            'identifier' => 'admin@example.com',
            '_csrf_token' => 'csrf',
        ]);
        $request->setSession($session);

        $passport = $authenticator->authenticate($request);

        $this->assertSame('admin@example.com', $passport->getBadge(UserBadge::class)?->getUserIdentifier());
    }

    public function testOnAuthenticationSuccessRedirectsToWelcome(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn(null);

        $request = Request::create('/login');
        $request->setSession($session);

        $token = $this->createStub(TokenInterface::class);

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertSame('/welcome', $response->getTargetUrl());
    }

    public function testOnAuthenticationSuccessRedirectsToTargetPath(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');
        $session = $this->createStub(SessionInterface::class);
        $session->method('get')
            ->with('_security.main.target_path')
            ->willReturn('/stores');

        $request = Request::create('/login');
        $request->setSession($session);

        $token = $this->createStub(TokenInterface::class);

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertSame('/stores', $response->getTargetUrl());
    }

    public function testGetLoginUrlReturnsLoginRoute(): void
    {
        $authenticator = new LoginFormAuthenticator($this->createRouter(), 'test');

        $method = new ReflectionMethod(LoginFormAuthenticator::class, 'getLoginUrl');

        $request = Request::create('/login');

        $this->assertSame('/login', $method->invoke($authenticator, $request));
    }
}
