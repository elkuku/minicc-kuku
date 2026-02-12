<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\DepositRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits', name: 'deposits_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    use PaginatorTrait;

    public function __construct(private readonly DepositRepository $depositRepository)
    {
    }

    public function __invoke(
        Request           $request,
        #[Autowire('%env(LIST_LIMIT)%')]
        int               $listLimit
    ): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request, $listLimit);
        $deposits = $this->depositRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(
                \count($deposits) / $paginatorOptions->getLimit()
            )
        );

        return $this->render(
            'deposit/list.html.twig',
            [
                'deposits' => $deposits,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }
}
