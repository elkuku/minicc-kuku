<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Repository\DepositRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits/search', name: 'deposits_search', methods: ['GET'])]
class Search extends BaseController
{
    public function __construct(private readonly DepositRepository $depositRepository)
    {
    }

    public function __invoke(
        #[MapQueryParameter(flags: FILTER_NULL_ON_FAILURE)]
        ?int $q = null,
    ): Response {
        $ids = $this->depositRepository->search($q ?? 0);

        return $this->render(
            'deposit/_search_result.html.twig',
            [
                'ids' => $ids,
            ]
        );
    }
}
