<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Type\GoogleUser;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @see \App\Tests\Security\GoogleIdentityAuthenticatorTest
 */
class GoogleIdentityAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private const string INVALID_TOKEN_MESSAGE = 'No se pudo verificar su cuenta de Google. Por favor intente de nuevo.';

    public function __construct(
        #[Autowire(env: 'OAUTH_GOOGLE_ID')]
        private readonly string $oauthGoogleId,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Override]
    public function supports(Request $request): bool
    {
        return '/connect/google/verify' === $request->getPathInfo();
    }

    #[Override]
    public function authenticate(Request $request): Passport
    {
        $this->checkCsrfToken($request);

        $idToken = (string) $request->request->get('credential');

        if ('' === $idToken || '0' === $idToken) {
            throw new CustomUserMessageAuthenticationException('No se recibieron las credenciales de Google. Por favor intente de nuevo.');
        }

        try {
            $payload = new Client([
                'client_id' => $this->oauthGoogleId,
            ])
                ->verifyIdToken($idToken);
        } catch (Throwable $throwable) {
            throw new CustomUserMessageAuthenticationException(self::INVALID_TOKEN_MESSAGE, [], 0, $throwable);
        }

        if (!$payload) {
            throw new CustomUserMessageAuthenticationException(self::INVALID_TOKEN_MESSAGE);
        }

        $user = $this->getUser(new GoogleUser($payload));

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
            [new RememberMeBadge()],
        );
    }

    /**
     * Google Identity Services sends a g_csrf_token both as a cookie and in the
     * POST body (double-submit cookie). Both must be present and match.
     */
    private function checkCsrfToken(Request $request): void
    {
        $cookieToken = (string) $request->cookies->get('g_csrf_token');
        $bodyToken = (string) $request->request->get('g_csrf_token');

        if ('' === $cookieToken || '' === $bodyToken || !hash_equals($cookieToken, $bodyToken)) {
            throw new CustomUserMessageAuthenticationException('La solicitud de inicio de sesión no es válida. Por favor intente de nuevo.');
        }
    }

    private function getUser(GoogleUser $googleUser): User
    {
        $user = $this->userRepository->findOneBy(
            [
                'googleId' => $googleUser->getId(),
            ]
        );

        if ($user) {
            return $user;
        }

        // @todo remove: Fetch user by email
        $user = $this->userRepository->findOneBy(
            [
                'email' => $googleUser->getEmail(),
            ]
        );

        if ($user) {
            // @todo remove: Update existing users google id
            $user->setGoogleId($googleUser->getId());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        }

        throw new CustomUserMessageAuthenticationException('La cuenta {{ email }} no tiene acceso. Por favor contacte al administrador.', ['{{ email }}' => htmlspecialchars((string) $googleUser->getEmail())]);
    }

    #[Override]
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): RedirectResponse {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('welcome'));
    }

    #[Override]
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): RedirectResponse {
        /**
         * @var Session $session
         */
        $session = $request->getSession();
        $session->getFlashBag()->add(
            'danger',
            $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security'),
        );

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
