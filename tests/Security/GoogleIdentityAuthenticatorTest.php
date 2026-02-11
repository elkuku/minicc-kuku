<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\GoogleIdentityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class GoogleIdentityAuthenticatorTest extends TestCase
{
    private function createAuthenticator(): GoogleIdentityAuthenticator
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(fn(string $route): string => '/' . $route);

        return new GoogleIdentityAuthenticator(
            'fake-google-client-id',
            $this->createStub(UserRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
        );
    }

    public function testSupportsReturnsTrueForGoogleVerifyPath(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/connect/google/verify', 'POST');

        self::assertTrue($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseForOtherPaths(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/login', 'POST');

        self::assertFalse($authenticator->supports($request));
    }

    public function testAuthenticateThrowsExceptionWhenNoCredentials(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/connect/google/verify', 'POST');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing credentials :(');

        $authenticator->authenticate($request);
    }

    public function testOnAuthenticationSuccessRedirectsToWelcome(): void
    {
        $authenticator = $this->createAuthenticator();
        $session = $this->createStub(Session::class);
        $session->method('get')->willReturn(null);

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $token = $this->createStub(TokenInterface::class);

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/welcome', $response->getTargetUrl());
    }

    public function testOnAuthenticationSuccessRedirectsToTargetPath(): void
    {
        $authenticator = $this->createAuthenticator();
        $session = $this->createStub(Session::class);
        $session->method('get')
            ->with('_security.main.target_path')
            ->willReturn('/stores');

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $token = $this->createStub(TokenInterface::class);

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertSame('/stores', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailureRedirectsToLoginWithFlash(): void
    {
        $authenticator = $this->createAuthenticator();

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects(self::once())
            ->method('add')
            ->with('danger', 'Auth failed');

        $session = $this->createStub(Session::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $exception = new AuthenticationException('Auth failed');

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }
}
