<?php

declare(strict_types=1);

namespace App\Controller\Users;

use App\Controller\BaseController;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/users', name: 'users_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    public function __construct(private readonly UserRepository $userRepo) {}

    public function __invoke(
        Request $request
    ): Response
    {
        $userActive = $request->request->get('user_active', '1');
        $userRole = (string) $request->request->get('user_role', '');
        $criteria = [];

        if ('0' === $userActive || '1' === $userActive) {
            $criteria['isActive'] = $userActive;
        } else {
            $userActive = null;
        }

        if ('' !== $userRole) {
            $role = UserRole::tryFrom($userRole);
            if ($role !== null) {
                $criteria['role'] = $role;
            } else {
                $userRole = '';
            }
        }

        return $this->render(
            'users/index.html.twig',
            [
                'users'      => $this->userRepo->findBy($criteria),
                'userActive' => $userActive,
                'userRole'   => $userRole,
                'roles'      => UserRole::cases(),
            ]
        );
    }
}
