<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/transactions', name: 'download_transactions', methods: ['GET'])]
class Transactions
{
    public function __invoke(
        TransactionRepository $transactionRepository,
        StoreRepository       $storeRepository,
        PdfHelper             $pdfHelper
    ): PdfResponse
    {
        $htmlPages = [];
        $year = (int)date('Y');
        $stores = $storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $pdfHelper->renderTransactionHtml(
                    $transactionRepository,
                    $store,
                    $year
                );
            }
        }

        $filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));

        return new PdfResponse(
            $pdfHelper->getOutputFromHtml(
                $htmlPages,
                [
                    'header-html' => $pdfHelper->getHeaderHtml(),
                    'footer-html' => $pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }
}
