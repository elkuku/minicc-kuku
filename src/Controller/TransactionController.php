<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionTypeType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Type\TransactionType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/transactions')]
class TransactionController extends AbstractController
{
    use PaginatorTrait;

    #[Route(path: '/delete/{id}', name: 'transaction-delete', methods: ['GET'])]
    public function delete(
        Request $request,
        Transaction $transaction,
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        $em = $managerRegistry->getManager();
        $em->remove($transaction);
        $em->flush();
        $this->addFlash('success', 'Transaction has been deleted');
        $redirect = str_replace('@', '/', $request->get('redirect'));

        return $this->redirect('/'.$redirect);
    }

    #[Route(path: '/edit/{id}', name: 'transaction-edit', methods: [
        'GET',
        'POST',
    ])]
    public function edit(
        Transaction $transaction,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $view = $request->query->get('view');
        $form = $this->createForm(TransactionTypeType::class, $transaction);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $em = $managerRegistry->getManager();
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

    #[Route(path: '/', name: 'transaction-rawlist', methods: ['GET', 'POST'])]
    public function rawList(
        StoreRepository $storeRepo,
        TransactionRepository $transactionRepo,
        Request $request,
        int $listLimit,
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request, $listLimit);
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
                'transactionTypes' => TransactionType::cases(),
                'stores'           => $storeRepo->findAll(),
            ]
        );
    }
}
