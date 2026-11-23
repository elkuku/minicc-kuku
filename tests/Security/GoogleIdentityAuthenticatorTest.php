<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\GoogleIdentityAuthenticator;
use App\Type\Gender;
use App\Type\GoogleUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GoogleIdentityAuthenticatorTest extends TestCase
{
    private function createAuthenticator(): GoogleIdentityAuthenticator
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(fn (string $route): string => '/'.$route);

        return new GoogleIdentityAuthenticator(
            'fake-google-client-id',
            $this->createStub(UserRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
            $this->createTranslator(),
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
        $request = $this->createVerifyRequest([]);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessageIsOrContains('No se recibieron las credenciales de Google');

        $authenticator->authenticate($request);
    }

    public function testAuthenticateThrowsWhenCsrfCookieIsMissing(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/connect/google/verify', Request::METHOD_POST, [
            'credential' => 'some-token',
            'g_csrf_token' => 'csrf-123',
        ]);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessageIsOrContains('La solicitud de inicio de sesión no es válida');

        $authenticator->authenticate($request);
    }

    public function testAuthenticateThrowsWhenCsrfTokensDoNotMatch(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create(
            '/connect/google/verify',
            Request::METHOD_POST,
            ['credential' => 'some-token', 'g_csrf_token' => 'csrf-123'],
            ['g_csrf_token' => 'other-token'],
        );

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessageIsOrContains('La solicitud de inicio de sesión no es válida');

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
            ->with('danger', 'La cuenta a@b.ec no tiene acceso.');

        $session = $this->createStub(Session::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $exception = new CustomUserMessageAuthenticationException('La cuenta {{ email }} no tiene acceso.', ['{{ email }}' => 'a@b.ec']);

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
                if (isset($criteria['googleId']) && 'google-123' === $criteria['googleId']) {
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

                if (isset($criteria['email']) && 'test@example.com' === $criteria['email']) {
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

        try {
            $this->callGetUser($authenticator, $googleUser);
            self::fail('Expected an authentication exception.');
        } catch (CustomUserMessageAuthenticationException $customUserMessageAuthenticationException) {
            self::assertSame(['{{ email }}' => 'unknown@example.com'], $customUserMessageAuthenticationException->getMessageData());
        }
    }

    public function testOnAuthenticationFailureTranslatesGenericException(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('An authentication exception occurred.', [], 'security')
            ->willReturn('Ocurrió un error de autenticación.');

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/login');

        $authenticator = new GoogleIdentityAuthenticator(
            'fake-google-client-id',
            $this->createStub(UserRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
            $translator,
        );

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('danger', 'Ocurrió un error de autenticación.');

        $session = $this->createStub(Session::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request = Request::create('/connect/google/verify');
        $request->setSession($session);

        $authenticator->onAuthenticationFailure($request, new AuthenticationException('internal details'));
    }

    public function testAuthenticateThrowsOnInvalidToken(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = $this->createVerifyRequest(['credential' => 'invalid-token']);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessageIsOrContains('No se pudo verificar su cuenta de Google');

        $authenticator->authenticate($request);
    }

    /**
     * @param array<string, string> $parameters
     */
    private function createVerifyRequest(array $parameters): Request
    {
        return Request::create(
            '/connect/google/verify',
            Request::METHOD_POST,
            $parameters + ['g_csrf_token' => 'csrf-123'],
            ['g_csrf_token' => 'csrf-123'],
        );
    }

    private function createTranslator(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnCallback(fn (string $id, array $parameters = []): string => strtr($id, $parameters));

        return $translator;
    }

    private function createAuthenticatorWith(
        UserRepository $userRepository,
        ?EntityManagerInterface $entityManager = null,
    ): GoogleIdentityAuthenticator {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturnCallback(fn (string $route): string => '/'.$route);

        return new GoogleIdentityAuthenticator(
            'fake-google-client-id',
            $userRepository,
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
            $this->createTranslator(),
        );
    }

    private function callGetUser(
        GoogleIdentityAuthenticator $authenticator,
        GoogleUser $googleUser,
    ): User {
        $method = new ReflectionMethod($authenticator, 'getUser');
        $result = $method->invoke($authenticator, $googleUser);
        self::assertInstanceOf(User::class, $result);

        return $result;
    }

    private function createTestUser(): User
    {
        return new User()
            ->setEmail('test@example.com')
            ->setName('Test User')
            ->setGender(Gender::male);
    }
}
