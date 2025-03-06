<?php

namespace App\Controller\Transactions;

use App\Controller\BaseController;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Type\TransactionType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/transactions', name: 'transactions_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    use PaginatorTrait;

    public function __invoke(
        StoreRepository       $storeRepo,
        TransactionRepository $transactionRepo,
        Request               $request,
        #[Autowire('%env(LIST_LIMIT)%')]
        int                   $listLimit,
    ): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request, $listLimit);
        $transactions = $transactionRepo->getRawList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(
                $transactions->count() / $paginatorOptions->getLimit()
            )
        );

        return $this->render(
            'transaction/index.html.twig',
            [
                'transactions' => $transactions,
                'paginatorOptions' => $paginatorOptions,
                'transactionTypes' => TransactionType::cases(),
                'stores' => $storeRepo->findAll(),
            ]
        );
    }
}
