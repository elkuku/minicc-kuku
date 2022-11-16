<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFullType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/users')]
class UserController extends AbstractController
{
    #[Route(path: '/', name: 'users-list', methods: ['GET', 'POST'])]
    public function list(
        UserRepository $userRepo,
        Request $request
    ): Response {
        $userActive = $request->get('user_active', '1');
        $criteria = [];
        if ('0' === $userActive || '1' === $userActive) {
            $criteria['isActive'] = $userActive;
        } else {
            $userActive = null;
        }

        return $this->render(
            'user/list.html.twig',
            [
                'users' => $userRepo->findBy($criteria),
                'userActive' => $userActive,
            ]
        );
    }

    #[Route(path: '/edit/{id}', name: 'user-edit', methods: ['GET', 'POST'])]
    public function edit(
        User $client,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $form = $this->createForm(UserFullType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();

            $em = $managerRegistry->getManager();
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

    #[Route(path: '/new', name: 'register', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $user = new User;
        $form = $this->createForm(UserFullType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole('ROLE_USER');

            $em = $managerRegistry->getManager();
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
}
