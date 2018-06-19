<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFullType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\UserStateRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/users")
 */
class UserController extends Controller
{
	/**
	 * @Route("/", name="users-list")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function list(UserRepository $userRepository, UserStateRepository $userStateRepository,
	                     Request $request): Response
	{
		$userState = (int) $request->get('user_state', 1);

		$criteria = ['role' => 'ROLE_USER'];

		if ($userState)
		{
			$criteria['state'] = $userStateRepository->find($userState);
		}

		return $this->render(
			'user/list.html.twig',
			[
				'users'     => $userRepository->findBy($criteria),
				'userState' => $userState,
				'states'    => $userStateRepository->findAll(),
			]
		);
	}

	/**
	 * @Route("/edit/{id}", name="user-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function edit(User $client, Request $request): Response
	{
		$form = $this->createForm(UserFullType::class, $client);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$client = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($client);
			$em->flush();

			$this->addFlash('success', 'El usuario ha sido guardado');

			return $this->redirectToRoute('users-list');
		}

		return $this->render(
			'user/form.html.twig',
			[
				'form' => $form->createView(),
				'data' => $client,
			]
		);
	}

	/**
	 * @Route("/pdf", name="pdf-users")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function pdfList(UserRepository $userRepository): Response
	{
		return $this->render(
			'user/user-pdf-list.html.twig',
			[
				'users' => $this->getSortedUsers($userRepository),
			]
		);
	}

	/**
	 * @Route("/ruclist", name="users-ruclist")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function rucList(UserRepository $userRepository): PdfResponse
	{
		$html = $this->renderView('user/ruclist.html.twig', ['users' => $this->getSortedUsers($userRepository)]);

		return new PdfResponse(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			sprintf('user-list-%s.pdf', date('Y-m-d'))
		);
	}

	/**
	 * Sort users by their store number(s).
	 */
	private function getSortedUsers(UserRepository $userRepository): array
	{
		$users = $userRepository->findActiveUsers();

		usort(
			$users,
			function ($a, $b) {
				$aId = 0;
				$bId = 0;

				/** @type \App\Entity\User $a */
				foreach ($a->getStores() as $store)
				{
					$aId = $store->getId();
				}

				/** @type \App\Entity\User $b */
				foreach ($b->getStores() as $store)
				{
					$bId = $store->getId();
				}

				return ($aId < $bId) ? -1 : 1;
			}
		);

		return $users;
	}

	/**
	 * @Route("/new", name="register")
	 *
	 * // NOTE: Only admin can register new users !
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function new(Request $request): Response
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
