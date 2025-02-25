<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\PhpXlsxGenerator;
use App\Service\TextFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/export')]
#[IsGranted('ROLE_ADMIN')]
class ExportController extends AbstractController
{
    #[Route(path: '/', name: 'app_export_users_to_excel', methods: ['GET'])]
    public function usersToExcel(
        UserRepository $userRepository,
        TextFormatter $textFormatter,
    ): RedirectResponse
    {
        $users = $userRepository->getSortedByStore();

        $rows = [];
        $rows[] = ['Nombre', 'Email', 'RUC', 'Direccion', 'Telefono'];

        foreach ($users as $user) {
            $rows[] = [$user->getName(),$user->getEmail(),$textFormatter->formatRUC($user), $user->getDireccion(), $user->getTelefono()];
        }

        $xlsx = PhpXlsxGenerator::fromArray( $rows );
        $xlsx->downloadAs('clientes-'.gmdate('Y-m-d') . '.xlsx');

        return $this->redirectToRoute('admin-tasks');
    }

}
