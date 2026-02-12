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
#[Route(path: '/payment-methods/edit/{id}', name: 'payment_methods_edit', methods: ['GET', 'POST'])]
class Edit extends BaseController
{
    public function __invoke(
        PaymentMethod $data,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $form = $this->createForm(PaymentMethodType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Payment method has been updated');

            return $this->redirectToRoute('payment_methods_index');
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'form.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            [
                'form' => $form,
                'data' => $data,
            ],
            new Response(null, $form->isSubmitted() ? 422 : 200)
        );
    }
}
