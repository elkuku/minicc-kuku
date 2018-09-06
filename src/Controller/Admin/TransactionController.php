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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TransactionController
 */
class TransactionController extends Controller
{
	/**
	 * @Route("/mail-transactions", name="mail-transactions")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function mail(StoreRepository $storeRepository, TransactionRepository $transactionRepository, Request $request)
	{
		$recipients = $request->get('recipients');

		$year = date('Y');

		if (!$recipients)
		{
			$this->addFlash('warning', 'No recipients selected');

			return $this->redirectToRoute('mail-list-transactions');
		}

		$stores = $storeRepository->getActive();
		$failures = [];
		$successes = [];

		foreach ($stores as $store)
		{
			if (array_key_exists($store->getId(), $recipients))
			{
				$fileName = "movimientos-{$store->getId()}-$year.pdf";
				$g = $store->getUser()->getGender()->getName();
				$html = $this->renderView(
					'_mail/client-transactions.twig',
					[
						'user' => $store->getUser(),
						'store' => $store,
						'fileName' => $fileName,
					]
				);

				$pdf = $this->get('knp_snappy.pdf')
					->getOutputFromHtml(
						$this->getTransactionsHtml($transactionRepository, $store, $year)
					);

				$count = 0;

				try
				{
					$message = (new \Swift_Message)
						->setSubject("Movimientos del local {$store->getId()} ano $year")
						->setFrom('minicckuku@gmail.com')
						->setTo($store->getUser()->getEmail())
						->setBody($html)
						->attach(new \Swift_Attachment($pdf, $fileName, 'application/pdf'));

					$count = $this->get('mailer')->send($message);
					$successes[] = $store->getId();
				}
				catch (\Exception $exception)
				{
					$failures[] = $exception->getMessage();
				}

				if (0 === $count)
				{
					$failures[] = 'Unable to send the message to store: ' . $store->getId();
				}
			}
		}

		if ($failures)
		{
			$this->addFlash('warning', implode('<br>', $failures));
		}

		if ($successes)
		{
			$this->addFlash('success', 'Mails have been sent to stores: ' . implode(', ', $successes));
		}

		return $this->redirectToRoute('welcome');
	}

	/**
	 * @Route("/store-transaction-pdf/{id}/{year}", name="store-transaction-pdf")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function getStore(Store $store, int $year, TransactionRepository $transactionRepository): PdfResponse
	{
		$html = $this->getTransactionsHtml($transactionRepository, $store, $year);

		$filename = sprintf('movimientos-%d-local-%d-%s.pdf', $year, $store->getId(), date('Y-m-d'));

		$header = $this->renderView(
			'_header-pdf.html.twig',
			[
				'rootPath' => $this->get('kernel')->getProjectDir() . '/public',
			]
		);

		$footer = $this->renderView('_footer-pdf.html.twig');

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml(
				$html,
				[
					'footer-html' => $footer,
					'header-html' => $header,
				]
			),
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

		foreach ($stores as $store)
		{
			if ($store->getUserId())
			{
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

		$pages   = (int) (\count($transactions) / $transactionsPerPage) + 1;
		$fillers = $transactionsPerPage - (\count($transactions) - ($pages - 1) * $transactionsPerPage);

		for ($i = 1; $i < $fillers; $i++)
		{
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
