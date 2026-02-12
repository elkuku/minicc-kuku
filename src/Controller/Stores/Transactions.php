<?php

declare(strict_types=1);

namespace App\Controller\Stores;

use App\Controller\BaseController;
use App\Entity\Store;
use App\Helper\BreadcrumbTrait;
use App\Helper\IntlConverter;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\ChartBuilderService;
use App\Service\TaxService;
use App\Type\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Transactions extends BaseController
{
    use BreadcrumbTrait;

    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly StoreRepository $storeRepository, private readonly TaxService $taxService, private readonly ChartBuilderService $chartBuilder) {}

    #[Route(path: '/stores/{id}', name: 'stores_transactions', requirements: ['id' => '\d+',], methods: ['GET', 'POST',])]
    public function show(
        Store $store,
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted('view', $store);
        $year = $request->query->getInt('year', (int)date('Y'));
        $this->addBreadcrumb('Stores', 'stores_index')
            ->addBreadcrumb('Store '.$store->getId());
        $transactions = $this->transactionRepository->findByStoreAndYear($store, $year);
        $headers = [];
        /** @var array<int, float> $monthPayments */
        $monthPayments = [];
        $rentalValues = [];
        $rentalValue = $this->taxService->addTax($store->getValAlq());
        for ($i = 1; $i < 13; ++$i) {
            $headers[] = IntlConverter::formatDate('1966-'.$i.'-1', 'MMMM');
            $monthPayments[$i] = 0;
            $rentalValues[$i] = $rentalValue;
        }

        foreach ($transactions as $transaction) {
            if (TransactionType::payment === $transaction->getType()) {
                $monthPayments[$transaction->getDate()->format('n')]
                    += (float)$transaction->getAmount();
            }
        }

        return $this->render(
            'stores/transactions.html.twig',
            [
                'transactions' => $transactions,
                'saldoAnterior' => $this->transactionRepository->getSaldoAnterior(
                    $store,
                    $year
                ),
                'headerStr' => json_encode($headers, JSON_THROW_ON_ERROR),
                'monthPayments' => json_encode(
                    array_values($monthPayments),
                    JSON_THROW_ON_ERROR
                ),
                'rentalValStr' => json_encode(
                    array_values($rentalValues),
                    JSON_THROW_ON_ERROR
                ),
                'store' => $store,
                'stores' => $this->storeRepository->findAll(),
                'year' => $year,
                'breadcrumbs' => $this->getBreadcrumbs(),
                'chart' => $this->chartBuilder->getStoreChart(
                    $headers,
                    array_values($monthPayments),
                    array_values($rentalValues)
                ),
            ]
        );
    }
}
