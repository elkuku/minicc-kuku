<?php

declare(strict_types=1);

namespace App\Controller\Transactions;

use App\Controller\BaseController;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[IsCsrfTokenValid('transaction_delete', tokenKey: '_token')]
#[Route(path: '/transactions/delete/{id}', name: 'transactions_delete', methods: ['POST'])]
class Delete extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        Request $request,
        Transaction $transaction,
    ): RedirectResponse
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
        $this->addFlash('success', 'Transaction has been deleted');

        $redirect = $this->sanitizeLocalRedirect($request->request->getString('view'));

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $this->redirectToRoute('transactions_index');
    }
}
