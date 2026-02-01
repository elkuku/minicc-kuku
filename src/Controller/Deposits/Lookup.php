<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Repository\DepositRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits/lookup', name: 'deposits_lookup', methods: ['GET'])]
class Lookup extends BaseController
{
    public function __invoke(
        DepositRepository $depositRepository,
        Request           $request
    ): JsonResponse
    {
        $id = $request->query->get('id');

        $deposit = $depositRepository->find($id);

        return $this->json($deposit);
    }

}
