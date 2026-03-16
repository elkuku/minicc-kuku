<?php

declare(strict_types=1);

namespace App\Controller\System;

use App\Controller\BaseController;
use App\Service\DeployLogParser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/system/logview', name: 'system_logview', methods: ['GET'])]
#[IsGranted('ROLE_ADMIN')]
class Logview extends BaseController
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly DeployLogParser $logParser,
    ) {}

    public function __invoke(
        #[Autowire('%kernel.project_dir%')] string $projectDir
    ): Response {
        $filesystem = new Filesystem();
        $filename = $projectDir.'/var/log/deploy.log';
        $entries = [];
        $error = '';

        try {
            if ($filesystem->exists($filename)) {
                $entries = $this->logParser->parse($filesystem->readFile($filename));
            } else {
                $error = 'No log file found!';
            }
        } catch (IOException $ioException) {
            $this->addFlash('danger', $ioException->getMessage());
        }

        $output = new BufferedOutput();

        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput(['command' => 'about']), $output);

        return $this->render('system/logview.html.twig', [
            'project_dir' => $projectDir,
            'logEntries' => array_reverse($entries),
            'error' => $error,
        ]);
    }
}
