<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 13.01.19
 * Time: 13:18
 */

namespace App\Service;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Knp\Snappy\Pdf;
use Twig\Environment;

class PdfHelper
{
    public function __construct(
        private readonly string $rootDir,
        private readonly Environment $twig,
        private readonly Pdf $pdfEngine,
    ) {
    }

    public function getRoot(): string
    {
        return $this->rootDir;
    }

    public function renderTransactionHtml(
        TransactionRepository $transactionRepository,
        Store $store,
        int $year,
        int $transactionsPerPage = 42
    ): string {
        $transactions = $transactionRepository->findByStoreAndYear(
            $store,
            $year
        );

        $pages = (int)(count($transactions) / $transactionsPerPage) + 1;
        $fillers = $transactionsPerPage - (count($transactions) - ($pages - 1)
                * $transactionsPerPage);

        for ($i = 1; $i < $fillers; $i++) {
            $transaction = new Transaction;
            $transactions[] = $transaction;
        }

        return $this->twig->render(
            '_pdf/transactions-pdf.html.twig',
            [
                'saldoAnterior' => $transactionRepository->getSaldoAnterior(
                    $store,
                    $year
                ),
                'transactions'  => $transactions,
                'store'         => $store,
                'year'          => $year,
            ]
        );
    }

    public function renderPayrollsHtml(
        int $year,
        int $month,
        PayrollHelper $payrollHelper,
        int $storeId = 0
    ): string {
        return $this->twig->render(
            '_pdf/payrolls-pdf.html.twig',
            $payrollHelper->getData($year, $month, $storeId)
        );
    }

    /**
     * @param array<string>|string $htmlPages
     * @param array<string, string|bool> $options
     *
     * @return string
     */
    public function getOutputFromHtml(
        array|string $htmlPages,
        array $options = []
    ): string {
        return $this->pdfEngine->getOutputFromHtml($htmlPages, $options);
    }

    public function getHeaderHtml(): string
    {
        return $this->twig->render(
            '_header-pdf.html.twig',
            [
                'rootPath' => $this->rootDir.'/public',
            ]
        );
    }

    public function getFooterHtml(): string
    {
        return $this->twig->render('_footer-pdf.html.twig');
    }
}
