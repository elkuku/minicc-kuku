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
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlanillasController
 */
class PlanillasController extends AbstractController
{
	/**
	 * @Route("/planillas-mail", name="planillas-mail")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function mail(StoreRepository $storeRepository, TransactionRepository $transactionRepository, Pdf $pdf, MailerInterface $mailer, KernelInterface $kernel): Response
	{
		$year  = date('Y');
		$month = date('m');

		$fileName = "planillas-$year-$month.pdf";
		$html     = 'Attachment: ' . $fileName;

		$document = $pdf->getOutputFromHtml(
			$this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository, $kernel)
		);

		$email = (new Email())
			->from('minicckuku@gmail.com')
			->to('minicckuku@gmail.com')
			->subject("NEW Planillas $year-$month")
			->html($html)
			->attach($document, "planillas-$year-$month.pdf");

		try
		{
			$mailer->send($email);
			$this->addFlash('success', 'Mail has been sent.');
		}
		catch (TransportExceptionInterface $e)
		{
			$this->addFlash('danger', 'ERROR sending mail: ' . $e->getMessage());
		}

		return $this->render('admin/tasks.html.twig');
	}

	/**
	 * @Route("/planilla-mail", name="planilla-mail")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function mailClients(StoreRepository $storeRepository, TransactionRepository $transactionRepository, Request $request, Pdf $pdf, \Swift_Mailer $mailer, KernelInterface $kernel): Response
	{
		$recipients = $request->get('recipients');

		if (!$recipients)
		{
			$this->addFlash('warning', 'No recipients selected');

			return $this->redirectToRoute('mail-list-transactions');
		}

		$year  = date('Y');
		$month = date('m');

		$fileName  = "planilla-$year-$month.pdf";
		$stores    = $storeRepository->getActive();
		$failures  = [];
		$successes = [];

		foreach ($stores as $store)
		{
			if (!array_key_exists($store->getId(), $recipients))
			{
				continue;
			}

			$document = $pdf->getOutputFromHtml(
				$this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository, $kernel, $store->getId())
			);

			$html = $this->renderView(
				'_mail/client-planillas.twig',
				[
					'user'     => $store->getUser(),
					'store'    => $store,
					'factDate' => "$year-$month-1",
					'fileName' => $fileName,
				]
			);

			$count = 0;

			try
			{
				$message = (new \Swift_Message)
					->setSubject("Planilla Local {$store->getId()} ($month - $year)")
					->setFrom('minicckuku@gmail.com')
					->setTo($store->getUser()->getEmail())
					->setBody($html)
					->attach(new \Swift_Attachment($document, $fileName, 'application/pdf'));

				$count       = $mailer->send($message);
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
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function download(StoreRepository $storeRepository, TransactionRepository $transactionRepository, Pdf $pdf, KernelInterface $kernel): PdfResponse
	{
		$year  = date('Y');
		$month = date('m');

		$filename = sprintf('planillas-%d-%d.pdf', $year, $month);
		$html     = $this->getPlanillasHtml($year, $month, $storeRepository, $transactionRepository, $kernel);

		return new PdfResponse(
			$pdf->getOutputFromHtml($html),
			$filename
		);
	}

	/**
	 * Get HTML
	 */
	private function getPlanillasHtml(int $year, int $month, StoreRepository $storeRepo, TransactionRepository $transactionRepo, KernelInterface $kernel, int $storeId = 0): string
	{
		$stores = $storeRepo->findAll();

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

			$storeData[$store->getId()]['saldoIni'] = $transactionRepo->getSaldoALaFecha(
				$store,
				$prevYear . '-' . $prevMonth . '-01'
			);

			$storeData[$store->getId()]['transactions'] = $transactionRepo->findMonthPayments(
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
				'rootPath'  => $kernel->getProjectDir() . '/public',
			]
		);
	}
}
