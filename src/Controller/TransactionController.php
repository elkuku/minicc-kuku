<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionTypeType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\TransactionTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/transactions')]
class TransactionController extends AbstractController
{
    use PaginatorTrait;

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/delete/{id}', name: 'transaction-delete')]
    public function delete(
        Request $request,
        Transaction $transaction
    ): Response {
        if (null === $transaction) {
            throw $this->createNotFoundException('No transaction found');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($transaction);
        $em->flush();
        $this->addFlash('success', 'Transaction has been deleted');
        $redirect = str_replace('@', '/', $request->get('redirect'));

        return $this->redirect('/'.$redirect);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/edit/{id}', name: 'transaction-edit')]
    public function edit(
        Transaction $transaction,
        Request $request
    ): Response {
        $view = $request->query->get('view');
        $form = $this->createForm(TransactionTypeType::class, $transaction);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($transaction);
            $em->flush();

            $this->addFlash('success', 'La Transaccion ha sido guardada.');

            return $this->redirectToRoute(
                'store-transactions',
                [
                    'id' => $transaction->getStore()->getId(),
                ]
            );
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
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/', name: 'transaction-rawlist')]
    public function rawList(
        StoreRepository $storeRepo,
        TransactionRepository $transactionRepo,
        TransactionTypeRepository $transactionTypeRepo,
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $transactions = $transactionRepo->getRawList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(
                $transactions->count() / $paginatorOptions->getLimit()
            )
        );

        return $this->render(
            'transaction/rawlist.html.twig',
            [
                'transactions'     => $transactions,
                'paginatorOptions' => $paginatorOptions,
                'transactionTypes' => $transactionTypeRepo->findAll(),
                'stores'           => $storeRepo->findAll(),
            ]
        );
    }
}
