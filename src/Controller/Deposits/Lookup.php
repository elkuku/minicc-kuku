<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Dto\DepositDto;
use App\Repository\DepositRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
        #[MapQueryParameter(flags: FILTER_NULL_ON_FAILURE)] ?int $id = null,
    ): JsonResponse {
        $deposit = null !== $id ? $this->depositRepository->find($id) : null;

        return $this->json(null !== $deposit ? DepositDto::fromDeposit($deposit) : null);
    }
}
