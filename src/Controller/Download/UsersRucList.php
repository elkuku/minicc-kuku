<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/users-ruc-list', name: 'download_users_ruc_list', methods: ['GET'])]
class UsersRucList extends BaseController
{
    public function __invoke(
        UserRepository $userRepository,
        PdfHelper      $pdfHelper,
    ): PdfResponse
    {
        $html = $this->renderView(
            '_pdf/ruclist.html.twig',
            [
                'users' => $userRepository->getSortedByStore(),
            ]
        );

        return new PdfResponse(
            $pdfHelper->getOutputFromHtml(
                $html,
                [
                    'header-html' => $pdfHelper->getHeaderHtml(),
                    'footer-html' => $pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }
}
