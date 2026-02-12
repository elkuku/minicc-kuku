<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/transactions', name: 'download_transactions', methods: ['GET'])]
class Transactions extends BaseController
{
    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly StoreRepository $storeRepository, private readonly PdfHelper $pdfHelper)
    {
    }

    public function __invoke(): PdfResponse
    {
        $htmlPages = [];
        $year = (int)date('Y');
        $stores = $this->storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $this->pdfHelper->renderTransactionHtml(
                    $this->transactionRepository,
                    $store,
                    $year
                );
            }
        }

        $filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));
        return new PdfResponse(
            $this->pdfHelper->getOutputFromHtml(
                $htmlPages,
                [
                    'header-html' => $this->pdfHelper->getHeaderHtml(),
                    'footer-html' => $this->pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }
}
