<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionTypeType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\TransactionTypeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionController
 *
 * @Route("/transactions")
 */
class TransactionController extends Controller
{
	use PaginatorTrait;

	/**
	 * @Route("/delete/{id}", name="transaction-delete")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function delete(Request $request, Transaction $transaction): Response
	{
		if (!$transaction)
		{
			throw $this->createNotFoundException('No transaction found');
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($transaction);
		$em->flush();

		$this->addFlash('success', 'Transaction has been deleted');

		$redirect = str_replace('@', '/', $request->get('redirect'));

		return $this->redirect('/' . $redirect);
	}

	/**
	 * @Route("/edit/{id}", name="transaction-edit")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function edit(Transaction $transaction, Request $request): Response
	{
		$view = $request->query->get('view');

		$form = $this->createForm(TransactionTypeType::class, $transaction);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$transaction = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($transaction);
			$em->flush();

			$this->addFlash('success', 'La Transaccion ha sido guardada.');

			return $this->redirectToRoute($view, ['id' => $transaction->getStore()->getId()]);
		}

		return $this->render(
			'transaction/form.html.twig',
			[
				'form'     => $form->createView(),
				'data'     => $transaction,
				'redirect' => $view,
			]
		);
	}

	/**
	 * @Route("/", name="transaction-rawlist")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function rawList(StoreRepository $storeRepository, TransactionRepository $transactionRepository,
		TransactionTypeRepository $transactionTypeRepository, Request $request
	): Response
	{
		$paginatorOptions = $this->getPaginatorOptions($request);

		$transactions = $transactionRepository->getRawList($paginatorOptions);

		$paginatorOptions->setMaxPages(ceil($transactions->count() / $paginatorOptions->getLimit()));

		return $this->render(
			'transaction/rawlist.html.twig',
			[
				'transactions'     => $transactions,
				'paginatorOptions' => $paginatorOptions,
				'transactionTypes' => $transactionTypeRepository->findAll(),
				'stores'           => $storeRepository->findAll(),
			]
		);
	}
}
