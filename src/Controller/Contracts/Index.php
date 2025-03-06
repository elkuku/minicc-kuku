<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts', name: 'contracts_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    public function __invoke(
        StoreRepository    $storeRepository,
        UserRepository     $userRepository,
        ContractRepository $contractRepository,
        Request            $request
    ): Response
    {
        $storeId = $request->request->getInt('store_id');
        $year = $request->request->getInt('year');

        return $this->render(
            'contracts/index.html.twig',
            [
                'stores' => $storeRepository->findAll(),
                'users' => $userRepository->findActiveUsers(),
                'contracts' => $contractRepository->findContracts($storeId, $year),
                'year' => $year,
                'storeId' => $storeId,
            ]
        );
    }
}
