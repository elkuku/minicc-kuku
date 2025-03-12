<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly RouterInterface $router,
        #[Autowire('%env(APP_ENV)%')]
        private readonly string          $appEnv
    )
    {
    }

    #[\Override]
    public function supports(Request $request): bool
    {
        if ('/login' !== $request->getPathInfo()) {
            return false;
        }

        return $request->isMethod('POST');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('login');
    }

    public function authenticate(Request $request): Passport
    {
        if (false === in_array($this->appEnv, ['dev', 'test'])) {
            throw new \UnexpectedValueException('GTFO!');
        }

        $credentials = $this->getCredentials($request);

        return new SelfValidatingPassport(
            new UserBadge($credentials['identifier']),
            [
                new CsrfTokenBadge('login', $credentials['csrf_token']),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request        $request,
        TokenInterface $token,
        string         $firewallName
    ): RedirectResponse
    {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('welcome'));
    }

    /**
     * @return array{identifier: string, csrf_token: string}
     */
    private function getCredentials(
        Request $request
    ): array
    {
        $credentials = [
            'identifier' => (string)$request->request->get('identifier'),
            'csrf_token' => (string)$request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $credentials['identifier']
        );

        return $credentials;
    }
}
