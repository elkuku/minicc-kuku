<?php

declare(strict_types=1);

namespace App\Controller\PaymentMethods;

use App\Controller\BaseController;
use App\Entity\PaymentMethod;
use App\Form\PaymentMethodType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/payment-methods/create', name: 'payment_methods_create', methods: ['GET', 'POST'])]
class Create extends BaseController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $paymentMethod = new PaymentMethod();
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paymentMethod);
            $entityManager->flush();

            $this->addFlash('success', 'Payment method has been saved');

            return $this->redirectToRoute('payment_methods_index');
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'form.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            [
                'form' => $form,
                'data' => $paymentMethod,
            ],
            new Response(null, $form->isSubmitted() ? 422 : 200)
        );
    }
}
