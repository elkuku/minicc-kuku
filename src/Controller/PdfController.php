<?php

namespace App\Controller;

use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\PayrollHelper;
use App\Service\PDFHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class PdfController extends AbstractController
{
    #[Route(path: '/store-transaction-pdf/{id}/{year}', name: 'store-transaction-pdf', methods: ['GET'])]
    public function getStore(
        Store $store,
        int $year,
        TransactionRepository $transactionRepository,
        Pdf $pdf,
        PDFHelper $PDFHelper
    ): PdfResponse {
        $this->denyAccessUnlessGranted('export', $store);
        $html = $PDFHelper->renderTransactionHtml(
            $transactionRepository,
            $store,
            $year
        );
        $filename = sprintf(
            'movimientos-%d-local-%d-%s.pdf',
            $year,
            $store->getId(),
            date('Y-m-d')
        );
        $header = $this->renderView(
            '_header-pdf.html.twig',
            [
                'rootPath' => $PDFHelper->getRoot().'/public',
            ]
        );
        $footer = $this->renderView('_footer-pdf.html.twig');

        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $html,
                [
                    'footer-html' => $footer,
                    'header-html' => $header,
                ]
            ),
            $filename
        );
    }

    #[Route(path: '/stores-transactions-pdf', name: 'stores-transactions-pdf', methods: ['GET'])]
    public function getStores(
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Pdf $pdf,
        PDFHelper $PDFHelper
    ): PdfResponse {
        $htmlPages = [];
        $year = (int)date('Y');
        $stores = $storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $PDFHelper->renderTransactionHtml(
                    $transactionRepository,
                    $store,
                    $year
                );
            }
        }
        $filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));

        return new PdfResponse(
            $pdf->getOutputFromHtml($htmlPages),
            $filename
        );
    }

    #[Route(path: '/planillas', name: 'planillas', methods: ['GET'])]
    public function download(
        Pdf $pdf,
        PDFHelper $PDFHelper,
        PayrollHelper $payrollHelper,
    ): PdfResponse {
        $year = (int)date('Y');
        $month = (int)date('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);

        $html = $PDFHelper->renderPayrollsHtml($year, $month, $payrollHelper);

        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $html,
                ['enable-local-file-access' => true]
            ),
            $filename
        );
    }


}
