<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use App\Form\PaymentMethodType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/payment-methods")
 */
class PaymentMethodController extends Controller
{
    /**
     * @Route("/", name="payment-methods", methods="GET")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function index(PaymentMethodRepository $repository): Response
    {
        return $this->render('payment-methods/list.html.twig', ['paymentMethods' => $repository->findAll()]);
    }

    /**
     * @Route("/new", name="payment-methods-new", methods="GET|POST")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
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
     * @Route("/edit/{id}", name="payment-methods-edit")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function edit(PaymentMethod $data, Request $request): Response
    {
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
     * @Route("/delete/{id}", name="payment-methods-delete")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function delete(PaymentMethod $paymentMethod): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($paymentMethod);
        $em->flush();

        $this->addFlash('success', 'Payment method has been deleted');

        return $this->redirectToRoute('payment-methods');
    }
}
