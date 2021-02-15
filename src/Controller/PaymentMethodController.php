<?php

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use App\Form\PaymentMethodType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/payment-methods')]
class PaymentMethodController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/', name: 'payment-methods', methods: ['GET'])]
    public function index(
        PaymentMethodRepository $repository
    ): Response {
        return $this->render(
            'payment-methods/list.html.twig',
            ['paymentMethods' => $repository->findAll()]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/new', name: 'payment-methods-new', methods: ['GET|POST'])]
    public function new(
        Request $request
    ): Response {
        $paymentMethod = new PaymentMethod;
        $form = $this->createForm(PaymentMethodType::class, $paymentMethod);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($paymentMethod);
            $em->flush();

            $this->addFlash('success', 'Payment method has been saved');

            return $this->redirectToRoute('payment-methods');
        }

        return $this->render(
            'payment-methods/form.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paymentMethod,
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/edit/{id}', name: 'payment-methods-edit')]
    public function edit(
        PaymentMethod $data,
        Request $request
    ): Response {
        $form = $this->createForm(PaymentMethodType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();

            $this->addFlash('success', 'Payment method has been saved');

            return $this->redirectToRoute('payment-methods');
        }

        return $this->render(
            'payment-methods/form.html.twig',
            [
                'form' => $form->createView(),
                'data' => $data,
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/delete/{id}', name: 'payment-methods-delete')]
    public function delete(
        PaymentMethod $paymentMethod
    ): Response {
        $em = $this->getDoctrine()->getManager();
        $em->remove($paymentMethod);
        $em->flush();
        $this->addFlash('success', 'Payment method has been deleted');

        return $this->redirectToRoute('payment-methods');
    }
}
