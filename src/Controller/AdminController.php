<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController
 */
class AdminController extends Controller
{
	/**
	 * @Route("/cobrar", name="cobrar")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function cobrarAction(Request $request)
	{
		$values = $request->request->get('values');
		$users  = $request->request->get('users');

		$storeRepo = $this->getDoctrine()->getRepository(Store::class);

		if ($values)
		{
			$em = $this->getDoctrine()->getManager();

			$type = $this->getDoctrine()
				->getRepository(TransactionType::class)
				// Type "Alquiler"
				->find(1);

			$userRepo = $this->getDoctrine()
				->getRepository(User::class);

			foreach ($values as $storeId => $value)
			{
				if (0 == $value)
				{
					// No value
					continue;
				}

				$transaction = (new Transaction)
					->setDate(new \DateTime($request->request->get('date_cobro')))
					->setStore($storeRepo->find((int) $storeId))
					->setUser($userRepo->find((int) $users[$storeId]))
					->setType($type)
					// Set negative value (!)
					->setAmount(-$value);

				$em->persist($transaction);
			}

			$em->flush();

			$this->addFlash('success', 'A cobrar...');

			return $this->redirectToRoute('welcome');
		}

		return $this->render('admin/cobrar.html.twig', ['stores' => $storeRepo->getActive()]);
	}

	/**
	 * @Route("/pay-day", name="pay-day")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function payDayAction(Request $request)
	{
		$payments = $request->request->get('payments');

		$storeRepo         = $this->getDoctrine()->getRepository(Store::class);
		$paymentMethodRepo = $this->getDoctrine()->getRepository(PaymentMethod::class);

		if (!$payments)
		{
			return $this->render(
				'admin/payday-html.twig',
				[
					'stores'         => $storeRepo->getActive(),
					'paymentMethods' => $paymentMethodRepo->findAll(),
				]
			);
		}

		$em = $this->getDoctrine()->getManager();

		$type = $this->getDoctrine()
			->getRepository(TransactionType::class)
			->findOneBy(['name' => 'Pago']);

		foreach ($payments['date_cobro'] as $i => $dateCobro)
		{
			if (!$dateCobro)
			{
				continue;
			}

			$store = $storeRepo->find((int) $payments['store'][$i]);

			if (!$store)
			{
				continue;
			}

			$method = $paymentMethodRepo->find((int) $payments['method'][$i]);

			$transaction = (new Transaction)
				->setDate(new \DateTime($dateCobro))
				->setStore($store)
				->setUser($store->getUser())
				->setType($type)
				->setMethod($method)
				->setRecipeNo((int) $payments['recipe'][$i])
				->setDocument((int) $payments['document'][$i])
				->setDepId((int) $payments['depId'][$i])
				->setAmount($payments['amount'][$i]);

			$em->persist($transaction);
		}

		$em->flush();

		$this->addFlash('success', 'Sa ha pagado...');

		return $this->redirectToRoute('welcome');
	}

	/**
	 * @Route("/pagos-por-ano", name="pagos-por-ano")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function pagosPorAnoAction(Request $request)
	{
		$year = $request->query->getInt('year') ?: date('Y');

		$transactions = $this->getDoctrine()
			->getRepository(Transaction::class)
			->getPagosPorAno($year);

		return $this->render(
			'admin/pagos-por-ano.html.twig',
			[
				'year'         => $year,
				'transactions' => $transactions,
			]
		);
	}

	/**
	 * @Route("/admin-tasks", name="admin-tasks")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function tasksAction()
	{
		return $this->render('admin/tasks.html.twig');
	}
}
