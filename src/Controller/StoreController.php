<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Store;
use App\Form\StoreType;
use App\Helper\BreadcrumbTrait;
use App\Helper\IntlConverter;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\TaxService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StoreController
 * @Route("/stores")
 */
class StoreController extends Controller
{
	use BreadcrumbTrait;

	/**
	 * @Route("/", name="stores-list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function index(StoreRepository $storeRepository): Response
	{
		return $this->render('stores/list.html.twig', ['stores' => $storeRepository->findAll()]);
	}

	/**
	 * @Route("/new", name="stores-add")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function new(Request $request): Response
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
	 * @Route("/edit/{id}", name="stores-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function edit(Store $store, Request $request): Response
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
	 * @Route("/{id}", name="store-transactions")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function show(TransactionRepository $transactionRepository, Store $store, Request $request, TaxService $taxService): Response
	{
		$year = (int) $request->get('year', date('Y'));

		$this->addBreadcrumb('Locales', 'stores-list')
			->addBreadcrumb('Local ' . $store->getId());

		$transactions = $transactionRepository->findByStoreAndYear($store, $year);

		$headers       = [];
		$monthPayments = [];
		$rentalValues  = [];
		$rentalValue   = $taxService->getValueConTax($store->getValAlq());

		for ($i = 1; $i < 13; $i++)
		{
			$headers[]         = IntlConverter::formatDate('1966-' . $i . '-1', 'MMMM');
			$monthPayments[$i] = 0;
			$rentalValues[$i]  = $rentalValue;
		}

		foreach ($transactions as $transaction)
		{
			if ($transaction->getType()->getName() === 'Pago')
			{
				$monthPayments[$transaction->getDate()->format('n')] += $transaction->getAmount();
			}
		}

		return $this->render(
			'stores/transactions.html.twig',
			[
				'transactions'  => $transactions,
				'saldoAnterior' => $transactionRepository->getSaldoAnterior($store, $year),
				'headerStr'     => "'" . implode("', '", $headers) . "'",
				'monthPayments' => implode(', ', $monthPayments),
				'rentalValStr'  => implode(', ', $rentalValues),
				'store'         => $store,
				'stores'        => $this->getDoctrine()->getRepository(Store::class)->findAll(),
				'year'          => $year,
				'breadcrumbs'   => $this->getBreadcrumbs(),
			]
		);
	}
}
