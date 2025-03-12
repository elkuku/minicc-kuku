<?php

declare(strict_types=1);

namespace App\Controller\PaymentMethods;

use App\Controller\BaseController;
use App\Repository\PaymentMethodRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/payment-methods', name: 'payment_methods_index', methods: ['GET'])]
class Index extends BaseController
{
    public function __invoke(
        PaymentMethodRepository $repository,
        Request                 $request
    ): Response
    {
        $template = $request->query->get('ajax')
            ? '_list.html.twig'
            : 'list.html.twig';

        return $this->render(
            'payment-methods/' . $template,
            [
                'paymentMethods' => $repository->findAll(),
            ]
        );
    }
}
