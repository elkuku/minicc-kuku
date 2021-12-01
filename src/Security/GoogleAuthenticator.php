<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/connect/google/check';
    }

    /**
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->getGoogleClient()->getAccessToken();

        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($token);

        $user = $this->getUser($googleUser);

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('default'));
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): RedirectResponse {
        $message = strtr(
            $exception->getMessageKey(),
            $exception->getMessageData()
        );
        $request->getSession()->getFlashBag()->add('danger', $message);

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    private function getUser(GoogleUser $googleUser): User
    {
        // 1) have they logged in with Google before? Easy!
        if ($user = $this->userRepository->findOneBy(
            ['googleId' => $googleUser->getId()]
        )
        ) {
            return $user;
        }

        // @todo remove: Fetch user by email
        if ($user = $this->userRepository->findOneBy(
            ['identifier' => $googleUser->getEmail()]
        )
        ) {
            // @todo remove: Update existing users google id
            $user->setGoogleId($googleUser->getId());
        } else {
            // Register new user
            $user = (new User())
                ->setUserIdentifier($googleUser->getEmail())
                ->setGoogleId($googleUser->getId());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function getGoogleClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }
}
