<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Entity\Store;
use App\Repository\TransactionRepository;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(path: '/download/store-transactions/{id}/{year}', name: 'download_store_transactions', methods: ['GET'])]
class StoreTransactions extends BaseController
{
    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly PdfHelper $pdfHelper)
    {
    }

    public function __invoke(
        Store                 $store,
        int                   $year
    ): PdfResponse
    {
        $this->denyAccessUnlessGranted('export', $store);
        $html = $this->pdfHelper->renderTransactionHtml(
            $this->transactionRepository,
            $store,
            $year
        );
        $filename = sprintf(
            'movimientos-%d-local-%d-%s.pdf',
            $year,
            $store->getId(),
            date('Y-m-d')
        );

        return new PdfResponse(
            $this->pdfHelper->getOutputFromHtml(
                $html,
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
