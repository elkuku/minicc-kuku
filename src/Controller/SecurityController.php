<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login', methods: ['GET'])]
    public function login(
        AuthenticationUtils $authenticationUtils
    ): Response {
        return $this->render(
            'auth/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error'         => $authenticationUtils->getLastAuthenticationError(
                ),
            ]
        );
    }

    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
    }
}
