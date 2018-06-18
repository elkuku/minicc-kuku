<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TransactionController
 */
class TransactionController extends Controller
{
    /**
     * @Route("/store-transaction-pdf/{id}/{year}", name="store-transaction-pdf")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getStore(TransactionRepository $transactionRepository, StoreRepository $storeRepository, Request $request): PdfResponse
    {
        $storeId = (int) $request->get('id');
        $year    = (int) $request->get('year', date('Y'));

        $store = $storeRepository->find($storeId);

        $html = $this->getTransactionsHtml($transactionRepository,$store, $year);

        $filename = sprintf('movimientos-%d-local-%d-%s.pdf', $year, $storeId, date('Y-m-d'));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            $filename
        );
    }

    /**
     * @Route("/stores-transactions-pdf", name="stores-transactions-pdf")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getStores(TransactionRepository $transactionRepository, StoreRepository $storeRepository): PdfResponse
    {
        $htmlPages = [];

        $year = date('Y');

        $stores = $storeRepository->findAll();

        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $this->getTransactionsHtml($transactionRepository, $store, $year);
            }
        }

        $filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($htmlPages),
            $filename
        );
    }

	/**
	 * Get HTML.
	 */
    private function getTransactionsHtml(TransactionRepository $transactionRepository, Store $store, int $year, int $transactionsPerPage = 42): string
    {
        $transactions = $transactionRepository->findByStoreAndYear($store, $year);

        $pages   = intval(count($transactions) / $transactionsPerPage) + 1;
        $fillers = $transactionsPerPage - (count($transactions) - ($pages - 1) * $transactionsPerPage);

        for ($i = 1; $i < $fillers; $i++) {
            $transaction    = new Transaction;
            $transactions[] = $transaction;
        }

        return $this->renderView(
            'stores/transactions-pdf.html.twig',
            [
                'saldoAnterior' => $transactionRepository->getSaldoAnterior($store, $year),
                'transactions'  => $transactions,
                'store'         => $store,
                'year'          => $year,
            ]
        );
    }
}
