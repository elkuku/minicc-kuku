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
 * @Security("is_granted('ROLE_ADMIN')")
 */
#[Route(path: '/users')]
class UserController extends AbstractController
{
    #[Route(path: '/', name: 'users-list', methods: ['GET', 'POST'])]
    public function list(
        UserRepository $userRepo,
        UserStateRepository $stateRepo,
        Request $request
    ): Response {
        $userState = (int)$request->get('user_state', 1);
        $criteria = [];
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

    #[Route(path: '/edit/{id}', name: 'user-edit', methods: ['GET', 'POST'])]
    public function edit(
        User $client,
        Request $request
    ): Response {
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

    #[Route(path: '/pdf', name: 'pdf-users', methods: ['GET'])]
    public function pdfList(
        UserRepository $userRepository
    ): Response {
        return $this->render(
            '_pdf/user-pdf-list.html.twig',
            [
                'users' => $this->getSortedUsers($userRepository),
            ]
        );
    }

    #[Route(path: '/ruclist', name: 'users-ruclist', methods: ['GET'])]
    public function rucList(
        UserRepository $userRepository,
        Pdf $pdf
    ): PdfResponse {
        $html = $this->renderView(
            '_pdf/ruclist.html.twig',
            ['users' => $this->getSortedUsers($userRepository)]
        );

        return new PdfResponse(
            $pdf->getOutputFromHtml($html),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }

    #[Route(path: '/new', name: 'register', methods: ['GET', 'POST'])]
    public function new(
        Request $request
    ): Response {
        $user = new User;
        $form = $this->createForm(UserFullType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole('ROLE_USER');

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
