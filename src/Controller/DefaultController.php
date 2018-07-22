<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use App\Service\TaxService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
	/**
	 * @Route("/", name="welcome")
	 */
	public function index(TransactionRepository $transactionRepository, TaxService $taxService): Response
	{
		$user    = $this->getUser();
		$headers = [];
		$data1   = [];
		$data2   = [];
		$saldos  = null;

		if ($user)
		{
			$saldos = $transactionRepository->getSaldos();

			foreach ($saldos as $saldo)
			{
				/** @var Transaction $transaction */
				$transaction = $saldo['data'];

				$headers[] = 'Local ' . $transaction->getStore()->getId();
				$data1[]   = round(-$saldo['amount'] / $taxService->getValueConTax($transaction->getStore()->getValAlq()), 1);
				$data2[]   = -$saldo['amount'];
			}
		}

		return $this->render(
			'default/index.html.twig',
			[
				'stores'        => $user ? $user->getStores() : null,
				'saldos'        => $saldos,
				'chart_headers' => json_encode($headers),
				'chart_data1'   => json_encode($data1),
				'chart_data2'   => json_encode($data2),
			]
		);
	}

	/**
	 * @Route("/about", name="about")
	 */
	public function about(): Response
	{
		return $this->render('default/about.html.twig');
	}

	/**
	 * @Route("/contact", name="contact")
	 */
	public function contact(): Response
	{
		return $this->render('default/contact.html.twig');
	}
}
