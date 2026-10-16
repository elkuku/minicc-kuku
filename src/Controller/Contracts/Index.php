<?php

declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Dto\ContractFilterPayload;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts', name: 'contracts_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly UserRepository $userRepository,
        private readonly ContractRepository $contractRepository,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] ContractFilterPayload $filter = new ContractFilterPayload(),
    ): Response {
        $storeId = $filter->storeId;
        $year = $filter->year;

        return $this->render(
            'contracts/index.html.twig',
            [
                'stores' => $this->storeRepository->findAll(),
                'users' => $this->userRepository->findActiveUsers(),
                'contracts' => $this->contractRepository->findContracts($storeId, $year),
                'year' => $year,
                'storeId' => $storeId,
            ]
        );
    }
}
