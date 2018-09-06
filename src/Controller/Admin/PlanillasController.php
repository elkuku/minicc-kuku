<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlanillasController
 */
class PlanillasController extends Controller
{
	/**
	 * @Route("/planillas-mail", name="planillas-mail")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function mail(StoreRepository $storeRepository, TransactionRepository $transactionRepository): Response
	{
		$year  = date('Y');
		$month = date('m');

		$fileName = "planillas-$year-$month.pdf";
		$html     = 'Attachment: ' . $fileName;

		$pdf = $this->get('knp_snappy.pdf')
			->getOutputFromHtml(
				$this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository)
			);

		$message = (new \Swift_Message)
			->setSubject("Planillas $year-$month")
			->setFrom('minicckuku@gmail.com')
			->setTo('minicckuku@gmail.com')
			->setBody($html)
			->attach(new \Swift_Attachment($pdf, $fileName, 'application/pdf'));

		$count = $this->get('mailer')->send($message);

		if (!$count)
		{
			$this->addFlash('danger', 'There was an error sending mail...');
		}
		else
		{
			$this->addFlash('success', ($count > 1 ? $count . ' mails have been sent.' : 'One mail has been sent.'));
		}

		return $this->render('admin/tasks.html.twig');
	}

	/**
	 * @Route("/planilla-mail", name="planilla-mail")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function mailClients(StoreRepository $storeRepository, TransactionRepository $transactionRepository, Request $request): Response
	{
		$recipients = $request->get('recipients');

		if (!$recipients)
		{
			$this->addFlash('warning', 'No recipients selected');

			return $this->redirectToRoute('mail-list-transactions');
		}

		$year  = date('Y');
		$month = date('m');

		$fileName = "planilla-$year-$month.pdf";
		$stores    = $storeRepository->getActive();
		$failures  = [];
		$successes = [];

		foreach ($stores as $store)
		{
			if (!array_key_exists($store->getId(), $recipients))
			{
				continue;
			}

			$pdf = $this->get('knp_snappy.pdf')
				->getOutputFromHtml(
					$this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository, $store->getId())
				);

			$html = $this->renderView(
				'_mail/client-planillas.twig',
				[
					'user' => $store->getUser(),
					'store' => $store,
					'factDate' => "$year-$month-1",
					'fileName' => $fileName,
				]
			);

			try
			{
				$message = (new \Swift_Message)
					->setSubject("Planilla Local {$store->getId()} ($month - $year)")
					->setFrom('minicckuku@gmail.com')
					->setTo($store->getUser()->getEmail())
					->setBody($html)
					->attach(new \Swift_Attachment($pdf, $fileName, 'application/pdf'));

				$count       = $this->get('mailer')->send($message);
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
	 * @Route("/planillas", name="planillas")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function download(StoreRepository $storeRepository, TransactionRepository $transactionRepository): PdfResponse
	{
		$year  = date('Y');
		$month = date('m');

		$filename = sprintf('planillas-%d-%d.pdf', $year, $month);
		$html     = $this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository);

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			$filename
		);
	}

	/**
	 * Get HTML
	 */
	private function getPlanillasHtml(int $year, int $month, StoreRepository $storeRepository, TransactionRepository $transactionRepository, int $storeId = 0): string
	{
		$stores = $storeRepository->findAll();

		$factDate = $year . '-' . $month . '-1';

		if (1 === $month)
		{
			$prevYear  = $year - 1;
			$prevMonth = 12;
		}
		else
		{
			$prevYear  = $year;
			$prevMonth = $month - 1;
		}

		$prevDate = $prevYear . '-' . $prevMonth . '-01';

		$storeData = [];
		$selecteds = [];

		/** @type Store $store */
		foreach ($stores as $store)
		{
			if ($storeId && $store->getId() !== $storeId)
			{
				continue;
			}

			$storeData[$store->getId()]['saldoIni'] = $transactionRepository->getSaldoALaFecha(
				$store,
				$prevYear . '-' . $prevMonth . '-01'
			);

			$storeData[$store->getId()]['transactions'] = $transactionRepository->findMonthPayments(
				$store,
				$prevMonth,
				$prevYear
			);

			$selecteds[] = $store;
		}

		return $this->renderView(
			'admin/planillas-pdf.html.twig',
			[
				'factDate'  => $factDate,
				'prevDate'  => $prevDate,
				'stores'    => $selecteds,
				'storeData' => $storeData,
				'rootPath'  => $this->get('kernel')->getProjectDir() . '/public',
			]
		);
	}
}
