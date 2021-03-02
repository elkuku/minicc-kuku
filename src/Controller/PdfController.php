<?php

namespace App\Controller;

use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class PdfController extends AbstractController
{
    #[Route(path: '/store-transaction-pdf/{id}/{year}', name: 'store-transaction-pdf', methods: ['GET'])]
    public function storeTransactions(
        Store $store,
        int $year,
        TransactionRepository $transactionRepository,
        PdfHelper $pdfHelper
    ): PdfResponse {
        $this->denyAccessUnlessGranted('export', $store);
        $html = $pdfHelper->renderTransactionHtml(
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

        return new PdfResponse(
            $pdfHelper->getOutputFromHtml(
                $html,
                [
                    'header-html'              => $pdfHelper->getHeaderHtml(),
                    'footer-html'              => $pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }

    #[Route(path: '/stores-transactions-pdf', name: 'stores-transactions-pdf', methods: ['GET'])]
    public function storesTransactions(
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        PdfHelper $pdfHelper
    ): PdfResponse {
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
                    'header-html'              => $pdfHelper->getHeaderHtml(),
                    'footer-html'              => $pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }

    #[Route(path: '/planillas', name: 'planillas', methods: ['GET'])]
    public function planillas(
        PdfHelper $PdfHelper,
        PayrollHelper $payrollHelper,
    ): PdfResponse {
        $year = (int)date('Y');
        $month = (int)date('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);

        $html = $PdfHelper->renderPayrollsHtml($year, $month, $payrollHelper);

        return new PdfResponse(
            $PdfHelper->getOutputFromHtml(
                $html,
                ['enable-local-file-access' => true]
            ),
            $filename
        );
    }

    #[Route(path: '/pdf', name: 'pdf-users', methods: ['GET'])]
    public function pdfList(
        UserRepository $userRepository
    ): Response {
        return $this->render(
            '_pdf/user-pdf-list.html.twig',
            ['users' => $userRepository->getSortedByStore()]
        );
    }

    #[Route(path: '/ruclist', name: 'users-ruclist', methods: ['GET'])]
    public function rucList(
        UserRepository $userRepository,
        PdfHelper $PdfHelper
    ): PdfResponse {
        $html = $this->renderView(
            '_pdf/ruclist.html.twig',
            ['users' => $userRepository->getSortedByStore()]
        );

        return new PdfResponse(
            $PdfHelper->getOutputFromHtml($html),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }
}
