<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Form\PaymentMethodType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ListController
 */
class PaymentMethodController extends Controller
{
	/**
	 * @Route("/payment-methods", name="payment-methods")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listAction()
	{
		$data = $this->getDoctrine()
			->getRepository(PaymentMethod::class)
			->findAll();

		return $this->render('payment-methods/list.html.twig', ['paymentMethods' => $data]);
	}

	/**
	 * @Route("/payment-methods-new", name="payment-methods-new")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function newAction(Request $request)
	{
		$data = new PaymentMethod();
		$form = $this->createForm(PaymentMethodType::class, $data);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
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
	 * @Route("/payment-methods-edit/{id}", name="payment-methods-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param PaymentMethod $data
	 * @param Request       $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function editAction(PaymentMethod $data, Request $request)
	{
		if (!$data)
		{
			throw $this->createNotFoundException('No payment method found');
		}

		$form = $this->createForm(PaymentMethodType::class, $data);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
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
	 * @Route("/payment-methods-delete/{id}", name="payment-methods-delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param PaymentMethod $paymentMethod
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function deletePaymentMethodAction(PaymentMethod $paymentMethod)
	{
		if (!$paymentMethod)
		{
			throw $this->createNotFoundException('No payment method found');
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($paymentMethod);
		$em->flush();

		$this->addFlash('success', 'Payment method has been deleted');

		return $this->redirectToRoute('payment-methods');
	}
}
