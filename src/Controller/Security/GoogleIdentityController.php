<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GoogleIdentityController extends AbstractController
{
    /**
     * This route is needed for the authenticator.
     */
    #[Route(path: '/connect/google/verify', name: 'connect_google_verify', methods: ['POST'])]
    public function connectVerify(): void
    {
        throw new \UnexpectedValueException(
            'Seems like the authenticator is missing :('
        );
    }
}
