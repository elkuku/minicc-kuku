<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UsersList extends BaseController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/download/users-list', name: 'download_users_list', methods: ['GET'])]
    public function pdfList(
        UserRepository $userRepository,
        PdfHelper      $PdfHelper,
    ): PdfResponse
    {
        $html = $this->renderView(
            '_pdf/user-pdf-list.html.twig',
            [
                'users' => $userRepository->getSortedByStore(),
            ]
        );

        return new PdfResponse(
            $PdfHelper->getOutputFromHtml($html),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }
}
