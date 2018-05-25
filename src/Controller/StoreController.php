<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Form\StoreType;
use App\Service\TaxService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StoreController
 */
class StoreController extends AbstractController
{
	/**
	 * @Route("/stores", name="stores-list")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listAction()
	{
		$stores = $this->getDoctrine()
			->getRepository(Store::class)
			->findAll();

		return $this->render('stores/list.html.twig', ['stores' => $stores]);
	}

	/**
	 * @Route("/store-add", name="stores-add")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function addAction(Request $request)
	{
		$store = new Store;
		$form  = $this->createForm(StoreType::class, $store);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$store = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($store);
			$em->flush();

			$this->addFlash('success', 'Store has been saved');

			return $this->redirectToRoute('stores-list');
		}

		return $this->render(
			'stores/form.html.twig',
			[
				'form'          => $form->createView(),
				'store'         => $store,
				'ivaMultiplier' => getenv('value_iva'),
			]
		);
	}

	/**
	 * @Route("/store-edit/{id}", name="stores-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Store   $store
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function editAction(Store $store, Request $request)
	{
		$form = $this->createForm(StoreType::class, $store);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$store = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($store);
			$em->flush();

			$this->addFlash('success', 'El local ha sido guardado.');

			return $this->redirectToRoute('stores-list');
		}

		return $this->render(
			'stores/form.html.twig',
			[
				'form'          => $form->createView(),
				'store'         => $store,
				'ivaMultiplier' => getenv('value_iva'),
			]
		);
	}

	/**
	 * @Route("/store/{id}", name="store-transactions")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Store      $store
	 * @param Request    $request
	 * @param TaxService $taxService
	 *
	 * @return Response
	 */
	public function listTransactionsAction(Store $store, Request $request, TaxService $taxService)
	{
		$year = (int) $request->get('year', date('Y'));

		$this->addBreadcrumb('Locales', 'stores-list')
			->addBreadcrumb('Local ' . $store->getId());

		$transactionRepo = $this->getDoctrine()
			->getRepository(Transaction::class);

		$transactions = $transactionRepo->findByStoreAndYear($store, $year);

		$monthPayments = [];
		$rentalValues  = [];
		$rentalValue   = $taxService->getValueConTax($store->getValAlq());

		for ($i = 1; $i < 13; $i++)
		{
			$monthPayments[$i] = 0;
			$rentalValues[$i]  = $rentalValue;
		}

		/** @type Transaction $transaction */
		foreach ($transactions as $transaction)
		{
			if ($transaction->getType()->getName() == 'Pago')
			{
				$monthPayments[$transaction->getDate()->format('n')] += $transaction->getAmount();
			}
		}

		$monthPayments = implode(', ', $monthPayments);

		return $this->render(
			'stores/transactions.html.twig',
			[
				'transactions'  => $transactions,
				'saldoAnterior' => $transactionRepo->getSaldoAnterior($store, $year),
				'monthPayments' => $monthPayments,
				'rentalValStr'  => implode(', ', $rentalValues),
				'store'         => $store,
				'stores'        => $this->getDoctrine()->getRepository(Store::class)->findAll(),
				'year'          => $year,
				'breadcrumbs'   => $this->getBreadcrumbs(),
			]
		);
	}
}
