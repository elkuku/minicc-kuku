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
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlanillasController
 */
class PlanillasController extends Controller
{
	/**
	 * @Route("/planillas-mail", name="planillas-mail")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function mailAction(): Response
	{
		$year  = date('Y');
		$month = date('m');

		$fileName = "planillas-$year-$month.pdf";
		$html     = "Attachment: " . $fileName;

		$pdf = $this->get('knp_snappy.pdf')
			->getOutputFromHtml(
				$this->getPlanillasHtml($year, $month)
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
	 * @Route("/planillas", name="planillas")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return PdfResponse
	 */
	public function downloadAction(): PdfResponse
	{
		$year  = date('Y');
		$month = date('m');

		$filename = sprintf('planillas-%d-%d.pdf', $year, $month);
		$html     = $this->getPlanillasHtml($year, $month);

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			$filename
		);
	}

	/**
	 * @param integer $year
	 * @param integer $month
	 *
	 * @return string
	 */
	private function getPlanillasHtml(int $year, int $month): string
	{
		$stores = $this->getDoctrine()
			->getRepository(Store::class)
			->findAll();

		$factDate = $year . '-' . $month . '-1';

		if (1 == $month)
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

		$repo = $this->getDoctrine()
			->getRepository(Transaction::class);

		$storeData = [];

		/** @type Store $store */
		foreach ($stores as $store)
		{
			$storeData[$store->getId()]['saldoIni']     = $repo->getSaldoALaFecha(
				$store,
				$prevYear . '-' . $prevMonth . '-01'
			);
			$storeData[$store->getId()]['transactions'] = $repo->findMonthPayments(
				$store,
				$prevMonth,
				$prevYear
			);
		}

		return $this->renderView(
			'admin/planillas-pdf.html.twig',
			[
				'factDate'  => $factDate,
				'prevDate'  => $prevDate,
				'stores'    => $stores,
				'storeData' => $storeData,
			]
		);
	}
}
