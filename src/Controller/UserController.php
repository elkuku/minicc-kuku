<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserState;
use App\Form\UserFullType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 */
class UserController extends Controller
{
	/**
	 * @Route("/users", name="users-list")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function listAction(Request $request): Response
	{
		$userState = (int) $request->get('user_state');

		$criteria = ['role' => 'ROLE_USER'];

		if ($userState)
		{
			$criteria['state'] = $this->getDoctrine()
				->getRepository(UserState::class)
				->find($userState);
		}

		$users = $this->getDoctrine()
			->getRepository(User::class)
			->findBy($criteria);

		$states = $this->getDoctrine()
			->getRepository(UserState::class)
			->findAll();

		return $this->render(
			'user/list.html.twig',
			[
				'users'     => $users,
				'userState' => $userState,
				'states'    => $states,
			]
		);
	}

	/**
	 * @Route("/users-pdf", name="pdf-users")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function pdfListAction(): Response
	{
		return $this->render(
			'user/user-pdf-list.html.twig',
			[
				'users' => $this->getSortedUsers(),
			]
		);
	}

	/**
	 * @Route("/user-edit/{id}", name="user-edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param User    $user
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function editAction(User $user, Request $request): Response
	{
		$form = $this->createForm(UserFullType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$user = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('success', 'El usuario ha sido guardado');

			return $this->redirectToRoute('users-list');
		}

		return $this->render(
			'user/form.html.twig',
			[
				'form' => $form->createView(),
				'data' => $user,
			]
		);
	}

	/**
	 * @Route("/users-ruclist", name="users-ruclist")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function rucListAction(): Response
	{
		$html = $this->renderView('user/ruclist.html.twig', ['users' => $this->getSortedUsers()]);

		$filename = sprintf('test-%s.pdf', date('Y-m-d'));

		return new Response(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			200,
			[
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * @return array
	 */
	private function getSortedUsers(): array
	{
		$users = $this->getDoctrine()
			->getRepository('App:User')
			->findActiveUsers();

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
}
