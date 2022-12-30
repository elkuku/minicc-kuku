<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFullType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(UserFullType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();

            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'El usuario ha sido guardado');

            return $this->redirectToRoute('users-list');
        }

        return $this->render(
            'user/form.html.twig',
            [
                'form' => $form,
                'data' => $client,
            ]
        );
    }

    #[Route(path: '/new', name: 'register', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = new User;
        $form = $this->createForm(UserFullType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole('ROLE_USER');

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'El usuario ha sido creado');

            return $this->redirectToRoute('users-list');
        }

        return $this->render(
            'user/form.html.twig',
            [
                'form' => $form,
                'data' => $user,
            ]
        );
    }
}
