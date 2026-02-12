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
    public function __construct(private readonly DepositRepository $depositRepository)
    {
    }

    public function __invoke(
        Request           $request
    ): JsonResponse
    {
        $id = $request->query->get('id');

        $deposit = $this->depositRepository->find($id);

        return $this->json($deposit);
    }

}
