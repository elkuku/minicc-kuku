<?php

namespace App\Controller\Transactions;

use App\Controller\BaseController;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/transactions/delete/{id}', name: 'transactions_delete', methods: ['GET'])]
class Delete extends BaseController
{
    public function __invoke(
        Request                $request,
        Transaction            $transaction,
        EntityManagerInterface $entityManager,
    ): RedirectResponse
    {
        $entityManager->remove($transaction);
        $entityManager->flush();
        $this->addFlash('success', 'Transaction has been deleted');
        $redirect = $request->get('view');

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $this->redirectToRoute('transactions_index');
    }
}
