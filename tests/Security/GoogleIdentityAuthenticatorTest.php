<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\GoogleIdentityAuthenticator;
use App\Type\Gender;
use App\Type\GoogleUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use UnexpectedValueException;

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
        $request = Request::create('/connect/google/verify', Request::METHOD_POST);

        $this->assertTrue($authenticator->supports($request));
    }

    public function testSupportsReturnsFalseForOtherPaths(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/login', Request::METHOD_POST);

        $this->assertFalse($authenticator->supports($request));
    }

    public function testAuthenticateThrowsExceptionWhenNoCredentials(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/connect/google/verify', Request::METHOD_POST);

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

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/welcome', $response->getTargetUrl());
    }

    public function testOnAuthenticationSuccessRedirectsToTargetPath(): void
    {
        $authenticator = $this->createAuthenticator();
        $session = $this->createStub(Session::class);
        $session->method('get')
            ->willReturn('/stores');

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $token = $this->createStub(TokenInterface::class);

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertSame('/stores', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailureRedirectsToLoginWithFlash(): void
    {
        $authenticator = $this->createAuthenticator();

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('danger', 'Auth failed');

        $session = $this->createStub(Session::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $exception = new AuthenticationException('Auth failed');

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testGetUserReturnsUserFoundByGoogleId(): void
    {
        $user = $this->createTestUser();

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($user): ?User {
                if (isset($criteria['googleId']) && $criteria['googleId'] === 'google-123') {
                    return $user;
                }

                return null;
            });

        $authenticator = $this->createAuthenticatorWith($userRepository);
        $googleUser = new GoogleUser([
            'sub' => 'google-123',
            'email' => 'test@example.com',
        ]);

        $result = $this->callGetUser($authenticator, $googleUser);

        $this->assertSame($user, $result);
    }

    public function testGetUserFoundByEmailUpdatesGoogleId(): void
    {
        $user = $this->createTestUser();

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($user): ?User {
                if (isset($criteria['googleId'])) {
                    return null;
                }

                if (isset($criteria['email']) && $criteria['email'] === 'test@example.com') {
                    return $user;
                }

                return null;
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        $authenticator = $this->createAuthenticatorWith($userRepository, $entityManager);
        $googleUser = new GoogleUser([
            'sub' => 'new-google-id',
            'email' => 'test@example.com',
        ]);

        $result = $this->callGetUser($authenticator, $googleUser);

        $this->assertSame($user, $result);
        $this->assertSame('new-google-id', $user->getGoogleId());
    }

    public function testGetUserThrowsWhenNotFound(): void
    {
        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findOneBy')->willReturn(null);

        $authenticator = $this->createAuthenticatorWith($userRepository);
        $googleUser = new GoogleUser([
            'sub' => 'unknown-id',
            'email' => 'unknown@example.com',
        ]);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('unknown@example.com');

        $this->callGetUser($authenticator, $googleUser);
    }

    public function testAuthenticateThrowsOnInvalidToken(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/connect/google/verify', Request::METHOD_POST, [
            'credential' => 'invalid-token',
        ]);

        $this->expectException(UnexpectedValueException::class);

        $authenticator->authenticate($request);
    }

    private function createAuthenticatorWith(
        UserRepository $userRepository,
        ?EntityManagerInterface $entityManager = null,
    ): GoogleIdentityAuthenticator {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(fn(string $route): string => '/' . $route);

        return new GoogleIdentityAuthenticator(
            'fake-google-client-id',
            $userRepository,
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
        );
    }

    private function callGetUser(
        GoogleIdentityAuthenticator $authenticator,
        GoogleUser $googleUser,
    ): User {
        $method = new ReflectionMethod($authenticator, 'getUser');

        return $method->invoke($authenticator, $googleUser);
    }

    private function createTestUser(): User
    {
        return new User()
            ->setEmail('test@example.com')
            ->setName('Test User')
            ->setGender(Gender::male);
    }
}
