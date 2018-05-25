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
use App\Entity\TransactionType;
use App\Form\TransactionTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionController
 */
class TransactionController extends AbstractController
{
	/**
	 * @Route("/transaction-delete/{id}", name="transaction-delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request     $request
	 * @param Transaction $transaction
	 *
	 * @return Response
	 */
	public function deleteTransactionAction(Request $request, Transaction $transaction)
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
	 * @Route("/transaction-edit/{id}", name="transaction-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function editAction(Request $request)
	{
		$view = $request->query->get('view');
		$id   = (int) $request->get('id');

		$data = $this->getDoctrine()
			->getRepository(Transaction::class)
			->find($id);

		$form = $this->createForm(TransactionTypeType::class, $data);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$data = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($data);
			$em->flush();

			$this->addFlash('success', 'La Transaccion ha sido guardada.');

			return $this->redirectToRoute($view, ['id' => $data->getStore()->getId()]);
		}

		return $this->render(
			'transaction/form.html.twig',
			[
				'form'     => $form->createView(),
				'data'     => $data,
				'redirect' => $view,
			]
		);
	}

	/**
	 * @Route("/transaction-rawlist", name="transaction-rawlist")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function rawListAction(Request $request)
	{
		$paginatorOptions = $this->getPaginatorOptions($request);

		$stores = $this->getDoctrine()
			->getRepository(Store::class)
			->findAll();

		$transactionTypes = $this->getDoctrine()
			->getRepository(TransactionType::class)
			->findAll();

		$transactions = $this->getDoctrine()
			->getRepository(Transaction::class)
			->getRawList($paginatorOptions);

		$paginatorOptions->setMaxPages(ceil($transactions->count() / $paginatorOptions->getLimit()));

		return $this->render(
			'transaction/rawlist.html.twig',
			[
				'transactions'     => $transactions,
				'paginatorOptions' => $paginatorOptions,
				'transactionTypes' => $transactionTypes,
				'stores'           => $stores,
			]
		);
	}
}
