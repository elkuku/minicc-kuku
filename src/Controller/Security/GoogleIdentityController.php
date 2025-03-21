<?php

declare(strict_types=1);

namespace App\Controller\Security;

use UnexpectedValueException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class GoogleIdentityController extends AbstractController
{
    /**
     * This route is needed for the authenticator.
     */
    #[Route(path: '/connect/google/verify', name: 'connect_google_verify', methods: ['POST'])]
    public function connectVerify(): never
    {
        throw new UnexpectedValueException('Seems like the authenticator is missing :(');
    }
}
