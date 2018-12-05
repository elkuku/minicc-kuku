<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\TransactionTypeRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 */
class AdminController extends AbstractController
{
	/**
	 * @Route("/cobrar", name="cobrar")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function cobrar(StoreRepository $storeRepository, UserRepository $userRepository,
	                       TransactionTypeRepository $transactionTypeRepository, Request $request
	): Response
	{
		$values = $request->request->get('values');
		$users  = $request->request->get('users');

		if ($values)
		{
			$em = $this->getDoctrine()->getManager();

			// Type "Alquiler"
			$type = $transactionTypeRepository->find(1);

			if (!$type)
			{
				throw new \UnexpectedValueException('Invalid transaction type');
			}

			foreach ($values as $storeId => $value)
			{
				if (0 === $value)
				{
					// No value
					continue;
				}

				$transaction = (new Transaction)
					->setDate(new \DateTime($request->request->get('date_cobro')))
					->setStore($storeRepository->find((int) $storeId))
					->setUser($userRepository->find((int) $users[$storeId]))
					->setType($type)
					// Set negative value (!)
					->setAmount(-$value);

				$em->persist($transaction);
			}

			$em->flush();

			$this->addFlash('success', 'A cobrar...');

			return $this->redirectToRoute('welcome');
		}

		return $this->render('admin/cobrar.html.twig', ['stores' => $storeRepository->getActive()]);
	}

	/**
	 * @Route("/pay-day", name="pay-day")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function payDay(StoreRepository $storeRepository, PaymentMethodRepository $paymentMethodRepository,
	                       TransactionTypeRepository $transactionTypeRepository, Request $request
	): Response
	{
		$payments = $request->request->get('payments');

		if (!$payments)
		{
			return $this->render(
				'admin/payday-html.twig',
				[
					'stores'         => $storeRepository->getActive(),
					'paymentMethods' => $paymentMethodRepository->findAll(),
				]
			);
		}

		$em = $this->getDoctrine()->getManager();

		$type = $transactionTypeRepository->findOneBy(['name' => 'Pago']);

		if (!$type)
		{
			throw new \UnexpectedValueException('Invalid transaction type');
		}

		foreach ($payments['date_cobro'] as $i => $dateCobro)
		{
			if (!$dateCobro)
			{
				continue;
			}

			$store = $storeRepository->find((int) $payments['store'][$i]);

			if (!$store)
			{
				continue;
			}

			$method = $paymentMethodRepository->find((int) $payments['method'][$i]);

			if (!$method)
			{
				throw new \UnexpectedValueException('Invalid payment method.');
			}

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
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function pagosPorAno(Request $request, TransactionRepository $repository): Response
	{
		$year = $request->query->getInt('year', (int) date('Y'));

		return $this->render(
			'admin/pagos-por-ano.html.twig',
			[
				'year'         => $year,
				'transactions' => $repository->getPagosPorAno($year),
			]
		);
	}

	/**
	 * @Route("/mail-list-transactions", name="mail-list-transactions")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function mailListTransactions(StoreRepository $storeRepository): Response
	{
		return $this->render(
			'admin/mail-list-transactions.twig',
			[
				'stores' => $storeRepository->getActive(),
			]
		);
	}

	/**
	 * @Route("/mail-list-planillas", name="mail-list-planillas")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function mailListPlanillas(StoreRepository $storeRepository): Response
	{
		return $this->render(
			'admin/mail-list-planillas.twig',
			[
				'stores' => $storeRepository->getActive(),
			]
		);
	}
}
