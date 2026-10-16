<?php

declare(strict_types=1);

namespace App\Controller\Users;

use App\Controller\BaseController;
use App\Dto\UserFilterPayload;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/users', name: 'users_index', methods: ['GET', 'POST'])]
class Index extends BaseController
{
    public function __construct(private readonly UserRepository $userRepo) {}

    public function __invoke(
        #[MapRequestPayload] UserFilterPayload $filter = new UserFilterPayload(),
    ): Response
    {
        $userActive = '' === $filter->userActive ? null : $filter->userActive;
        $userRole = $filter->userRole->value ?? '';
        $criteria = [];

        if (null !== $userActive) {
            $criteria['isActive'] = $userActive;
        }

        if ($filter->userRole instanceof UserRole) {
            $criteria['role'] = $filter->userRole;
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
