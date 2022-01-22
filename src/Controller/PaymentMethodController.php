<?php

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Form\PaymentMethodType;
use App\Repository\PaymentMethodRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
#[Route(path: '/payment-methods')]
class PaymentMethodController extends AbstractController
{
    #[Route(path: '/', name: 'payment-methods', methods: ['GET'])]
    public function index(
        PaymentMethodRepository $repository,
        Request $request
    ): Response {
        $template = $request->query->get('ajax')
            ? '_list.html.twig'
            : 'list.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            ['paymentMethods' => $repository->findAll()]
        );
    }

    #[Route(path: '/new', name: 'payment-methods-new', methods: [
        'GET',
        'POST',
    ])]
    public function new(
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $paymentMethod = new PaymentMethod;
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod = $form->getData();

            $em = $managerRegistry->getManager();
            $em->persist($paymentMethod);
            $em->flush();

            $this->addFlash('success', 'Payment method has been saved');

            return $this->redirectToRoute('payment-methods');
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'form.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            [
                'form' => $form->createView(),
                'data' => $paymentMethod,
            ],
            new Response(null, $form->isSubmitted() ? 422 : 200)
        );
    }

    #[Route(path: '/edit/{id}', name: 'payment-methods-edit', methods: [
        'GET',
        'POST',
    ])]
    public function edit(
        PaymentMethod $data,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $form = $this->createForm(PaymentMethodType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $em = $managerRegistry->getManager();
            $em->persist($data);
            $em->flush();

            $this->addFlash('success', 'Payment method has been updated');

            return $this->redirectToRoute('payment-methods');
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'form.html.twig';

        return $this->render(
            'payment-methods/'.$template,
            [
                'form' => $form->createView(),
                'data' => $data,
            ],
            new Response(null, $form->isSubmitted() ? 422 : 200)
        );
    }

    #[Route(path: '/delete/{id}', name: 'payment-methods-delete', methods: ['GET'])]
    public function delete(
        PaymentMethod $paymentMethod,
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        $em = $managerRegistry->getManager();
        $em->remove($paymentMethod);
        $em->flush();
        $this->addFlash('success', 'Payment method has been deleted');

        return $this->redirectToRoute('payment-methods');
    }
}
