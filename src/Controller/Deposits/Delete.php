<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits/delete/{id}', name: 'deposits_delete', methods: ['GET'])]
class Delete extends BaseController
{
    public function __invoke(
        Deposit                $deposit,
        EntityManagerInterface $entityManager,
    ): RedirectResponse
    {
        $entityManager->remove($deposit);
        $entityManager->flush();
        $this->addFlash('success', 'Deposit method has been deleted');

        return $this->redirectToRoute('deposits_index');
    }
}
