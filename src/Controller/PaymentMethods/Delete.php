<?php

declare(strict_types=1);

namespace App\Controller\PaymentMethods;

use App\Controller\BaseController;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/payment-methods/delete/{id}', name: 'payment_methods_delete', methods: ['GET'])]
class Delete extends BaseController
{
    public function __invoke(
        PaymentMethod          $paymentMethod,
        EntityManagerInterface $entityManager,
    ): RedirectResponse
    {
        $entityManager->remove($paymentMethod);
        $entityManager->flush();
        $this->addFlash('success', 'Payment method has been deleted');

        return $this->redirectToRoute('payment_methods_index');
    }
}
