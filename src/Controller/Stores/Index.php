<?php

namespace App\Controller\Stores;

use App\Controller\BaseController;
use App\Repository\StoreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CASHIER')]
#[Route(path: '/stores', name: 'stores_index', methods: ['GET'])]
class Index extends BaseController
{
    public function __invoke(
        StoreRepository $storeRepository
    ): Response
    {
        return $this->render(
            'stores/list.html.twig',
            [
                'stores' => $storeRepository->findAll(),
            ]
        );
    }
}
