<?php

declare(strict_types=1);

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
#[Route(path: '/users/edit/{id}', name: 'users_edit', methods: ['GET', 'POST'])]
class Edit extends BaseController
{
    public function __invoke(
        User                   $client,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $form = $this->createForm(UserFullType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();

            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'El usuario ha sido guardado');

            return $this->redirectToRoute('users_index');
        }

        return $this->render(
            'users/form.html.twig',
            [
                'form' => $form,
                'data' => $client,
            ]
        );
    }
}
