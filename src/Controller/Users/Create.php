<?php

namespace App\Controller\Users;

use App\Controller\BaseController;
use App\Entity\User;
use App\Form\UserFullType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/users/create', name: 'users_create', methods: ['GET', 'POST'])]
class Create extends BaseController
{
    public function __invoke(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserFullType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole('ROLE_USER');

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'El usuario ha sido creado');

            return $this->redirectToRoute('users_index');
        }

        return $this->render(
            'users/form.html.twig',
            [
                'form' => $form,
                'data' => $user,
            ]
        );
    }
}
