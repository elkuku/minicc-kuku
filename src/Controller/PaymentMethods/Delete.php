<?php

declare(strict_types=1);

namespace App\Controller\PaymentMethods;

use App\Controller\BaseController;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[IsCsrfTokenValid('payment_method_delete', tokenKey: '_token')]
#[Route(path: '/payment-methods/delete/{id}', name: 'payment_methods_delete', methods: ['POST'])]
class Delete extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        PaymentMethod $paymentMethod,
    ): RedirectResponse
    {
        $this->entityManager->remove($paymentMethod);
        $this->entityManager->flush();
        $this->addFlash('success', 'Payment method has been deleted');

        return $this->redirectToRoute('payment_methods_index');
    }
}
