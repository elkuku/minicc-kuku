<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/payments', name: 'admin_payments', methods: ['GET'])]
#[IsGranted('ROLE_CASHIER')]
class Payments extends BaseController
{
    public function __invoke(
        Request               $request,
        TransactionRepository $repository
    ): Response
    {
        $year = $request->query->getInt('year', (int)date('Y'));
        $month = $year === (int)date('Y') ? (int)date('m') : 1;

        return $this->render(
            'admin/payments.html.twig',
            [
                'year' => $year,
                'month' => $month,
                'transactions' => $repository->getPagosPorAno($year),
            ]
        );
    }
}
