<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RegistrationController
 */
class RegistrationController extends Controller
{
	/**
	 * @Route("/register", name="register")
	 *
	 * // NOTE: Only admin can register new users !!s
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function registerAction(Request $request)
	{
		// Create a new blank user and process the form
		$user = new User;
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			// Encode the new users password
			$encoder  = $this->get('security.password_encoder');
			$password = $encoder->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);

			// Set their role
			$user->setRole('ROLE_USER');

			// Save
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			return $this->redirectToRoute('login');
		}

		return $this->render(
			'auth/register.html.twig',
			[
				'form' => $form->createView(),
			]
		);
	}
}
