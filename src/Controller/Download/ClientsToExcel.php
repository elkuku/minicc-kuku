<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Service\PhpXlsxGenerator;
use App\Service\TextFormatter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/clients-to-excel', name: 'download_clients_to_excel', methods: ['GET'])]
class ClientsToExcel extends BaseController
{
    public function __construct(private readonly UserRepository $userRepository, private readonly TextFormatter $textFormatter) {}

    public function __invoke(): RedirectResponse
    {
        $users = $this->userRepository->getSortedByStore();
        $rows = [];
        $rows[] = ['Nombre', 'Email', 'RUC', 'Direccion', 'Telefono'];
        foreach ($users as $user) {
            $rows[] = [$user->getName(), $user->getEmail(), $this->textFormatter->formatRUC($user), $user->getDireccion(), $user->getTelefono()];
        }

        $xlsx = PhpXlsxGenerator::fromArray($rows);
        $xlsx->downloadAs('clientes-'.gmdate('Y-m-d').'.xlsx');
        return $this->redirectToRoute('admin_tasks');
    }
}
