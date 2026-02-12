<?php

declare(strict_types=1);

namespace App\Controller\Users;

use App\Controller\BaseController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/users', name: 'users_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    public function __construct(private readonly UserRepository $userRepo)
    {
    }

    public function __invoke(
        Request        $request
    ): Response
    {
        $userActive = $request->query->get('user_active', '1');
        $criteria = [];
        if ('0' === $userActive || '1' === $userActive) {
            $criteria['isActive'] = $userActive;
        } else {
            $userActive = null;
        }

        return $this->render(
            'users/index.html.twig',
            [
                'users' => $this->userRepo->findBy($criteria),
                'userActive' => $userActive,
            ]
        );
    }
}
