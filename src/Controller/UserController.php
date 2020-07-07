<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFullType;
use App\Repository\UserRepository;
use App\Repository\UserStateRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="users-list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function list(UserRepository $userRepo, UserStateRepository $stateRepo, Request $request): Response
    {
        $userState = (int)$request->get('user_state', 1);

        $criteria = [];// ['role' => 'ROLE_USER'];

        if ($userState) {
            $criteria['state'] = $stateRepo->find($userState);
        }

        return $this->render(
            'user/list.html.twig',
            [
                'users'     => $userRepo->findBy($criteria),
                'userState' => $userState,
                'states'    => $stateRepo->findAll(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="user-edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(User $client, Request $request): Response
    {
        $form = $this->createForm(UserFullType::class, $client);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function rucList(UserRepository $userRepository, Pdf $pdf): PdfResponse
    {
        $html = $this->renderView('user/ruclist.html.twig', ['users' => $this->getSortedUsers($userRepository)]);

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }

    /**
     * @Route("/new", name="register")
     *
     * // NOTE: Only admin can register new users !
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
        // Create a new blank user and process the form
        $user = new User;
        $form = $this->createForm(UserFullType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set their role
            $user->setRole('ROLE_USER');

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'El usuario ha sido creado');

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

    private function getSortedUsers(UserRepository $userRepository): array
    {
        $users = $userRepository->findActiveUsers();

        usort(
            $users,
            static function ($a, $b) {
                $aId = 0;
                $bId = 0;

                /** @type User $a */
                foreach ($a->getStores() as $store) {
                    $aId = $store->getId();
                }

                /** @type User $b */
                foreach ($b->getStores() as $store) {
                    $bId = $store->getId();
                }

                return ($aId < $bId) ? -1 : 1;
            }
        );

        return $users;
    }
}
