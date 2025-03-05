<?php

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Repository\DepositRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '//deposits/search', name: 'deposits_search', methods: ['GET'])]
class Search extends BaseController
{
    public function __invoke(
        DepositRepository $depositRepository,
        Request           $request
    ): Response
    {
        $documentId = (int)$request->get('q');
        $ids = $depositRepository->search($documentId);

        return $this->render(
            'deposit/_search_result.html.twig',
            [
                'ids' => $ids,
            ]
        );
    }
}
