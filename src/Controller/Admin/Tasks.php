<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin/tasks', name: 'admin_tasks', methods: ['GET'])]
class Tasks extends BaseController
{
    public function __invoke(): Response
    {
        return $this->render('admin/tasks.html.twig');
    }
}
