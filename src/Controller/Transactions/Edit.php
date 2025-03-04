<?php

namespace App\Controller\Transactions;

use App\Controller\BaseController;
use App\Entity\Transaction;
use App\Form\TransactionTypeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/transactions/edit/{id}', name: 'transactions_edit', methods: ['GET', 'POST'])]
class Edit extends BaseController
{
    public function __invoke(
        Transaction $transaction,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $view = $request->query->get('view');
        $form = $this->createForm(TransactionTypeType::class, $transaction);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'La Transaccion ha sido guardada.');

            if ($view) {
                return $this->redirect((string) $view);
            }

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
                'form' => $form,
                'data' => $transaction,
                'redirect' => $view,
            ]
        );
    }
}
