<?php

declare(strict_types=1);

namespace App\Controller\PaymentMethods;

use App\Controller\BaseController;
use App\Repository\PaymentMethodRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/payment-methods', name: 'payment_methods_index', methods: ['GET'])]
class Index extends BaseController
{
    public function __construct(private readonly PaymentMethodRepository $repository)
    {
    }

    public function __invoke(
        #[MapQueryParameter] bool $ajax = false,
    ): Response {
        $template = $ajax
            ? '_list.html.twig'
            : 'list.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            [
                'paymentMethods' => $this->repository->findAll(),
            ]
        );
    }
}
