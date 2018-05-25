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
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PdfController
 */
class PdfController extends Controller
{
	/**
	 * @Route("/store-transaction-pdf/{id}/{year}", name="store-transaction-pdf")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return PdfResponse
	 */
	public function transactionPdfAction(Request $request)
	{
		$storeId = (int) $request->get('id');
		$year    = (int) $request->get('year', date('Y'));

		$store = $this->getDoctrine()
			->getRepository(Store::class)
			->find($storeId);

		$html = $this->getTransactionsHtml($store, $year);

		$filename = sprintf('movimientos-%d-local-%d-%s.pdf', $year, $storeId, date('Y-m-d'));

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			$filename
		);
	}

	/**
	 * @Route("/stores-transactions-pdf", name="stores-transactions-pdf")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return PdfResponse
	 */
	public function transactionAllPdfAction()
	{
		$htmlPages = [];

		$year = date('Y');

		$stores = $this->getDoctrine()
			->getRepository(Store::class)
			->findAll();

		foreach ($stores as $store)
		{
			if (!$store->getUserId())
			{
				continue;
			}

			$htmlPages[] = $this->getTransactionsHtml($store, $year);
		}

		$filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($htmlPages),
			$filename
		);
	}

	/**
	 * @param Store $store
	 * @param int   $year
	 * @param int   $transactionsPerPage
	 *
	 * @return string
	 */
	private function getTransactionsHtml(Store $store, int $year, int $transactionsPerPage = 42)
	{

		$transactionRepo = $this->getDoctrine()
			->getRepository(Transaction::class);

		$transactions = $transactionRepo->findByStoreAndYear($store, $year);

		$pages   = intval(count($transactions) / $transactionsPerPage) + 1;
		$fillers = $transactionsPerPage - (count($transactions) - ($pages - 1) * $transactionsPerPage);

		for ($i = 1; $i < $fillers; $i++)
		{
			$transaction    = new Transaction();
			$transactions[] = $transaction;
		}

		return $this->renderView(
			'stores/transactions-pdf.html.twig',
			[
				'saldoAnterior' => $transactionRepo->getSaldoAnterior($store, $year),
				'transactions'  => $transactions,
				'store'         => $store,
				'year'          => $year,
			]
		);
	}
}
