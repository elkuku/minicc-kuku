<?php

namespace App\Security;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use UnexpectedValueException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private RouterInterface $router,
        private string $appEnv
    ) {
    }

    public function supports(Request $request): bool
    {
        return '/login' === $request->getPathInfo()
            && $request->isMethod('POST');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('login');
    }

    public function authenticate(Request $request): Passport
    {
        if (false === in_array($this->appEnv, ['dev', 'test'])) {
            throw new UnexpectedValueException('GTFO!');
        }

        $credentials = $this->getCredentials($request);

        return new SelfValidatingPassport(
            new UserBadge($credentials['identifier'] ?? ''),
            [new CsrfTokenBadge('login', $credentials['csrf_token'] ?? '')]
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

        return new RedirectResponse($this->router->generate('welcome'));
    }

    #[ArrayShape(['identifier' => "string", 'csrf_token' => "string"])]
    private function getCredentials(
        Request $request
    ): array {
        $credentials = [
            'identifier' => $request->request->get('identifier'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['identifier']
        );

        return $credentials;
    }
}
