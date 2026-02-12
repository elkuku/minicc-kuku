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
    public function __construct(private readonly UserRepository $userRepository, private readonly PdfHelper $pdfHelper)
    {
    }

    public function __invoke(): PdfResponse
    {
        $html = $this->renderView(
            '_pdf/ruclist.html.twig',
            [
                'users' => $this->userRepository->getSortedByStore(),
            ]
        );
        return new PdfResponse(
            $this->pdfHelper->getOutputFromHtml(
                $html,
                [
                    'header-html' => $this->pdfHelper->getHeaderHtml(),
                    'footer-html' => $this->pdfHelper->getFooterHtml(),
                    'enable-local-file-access' => true,
                ]
            ),
            sprintf('user-list-%s.pdf', date('Y-m-d'))
        );
    }
}
