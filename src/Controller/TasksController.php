<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use function dirname;

#[IsGranted('ROLE_ADMIN')]
class TasksController extends AbstractController
{
    #[Route(path: '/admin-tasks', name: 'admin-tasks', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/tasks.html.twig');
    }

    #[Route(path: '/console-view/{item}', name: 'console-view', methods: ['GET'])]
    public function consoleView(
        string $item,
        Request $request,
        KernelInterface $kernel
    ): Response {
        $command = [];
        switch ($item) {
            case 'routes':
                $command['command'] = 'debug:router';
                break;
            case 'route-match':
                $command['command'] = 'router:match';
                $command['path_info'] = $request->request->get('route');
                break;
            case 'migrations':
                $command['command'] = 'doctrine:migrations:status';
                break;
            case 'security':
                $command['command'] = 'security:check';
                $command['lockfile'] = dirname($kernel->getProjectDir());
                break;
            default:
                throw new UnexpectedValueException('Unknown command');
        }
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput($command);
        $output = new BufferedOutput;
        $application->run($input, $output);

        return $this->render(
            'admin/tasks.html.twig',
            [
                'consoleCommand' => $command,
                'consoleOutput'  => $output->fetch(),
            ]
        );
    }

    #[Route(path: '/sysinfo', name: 'sysinfo', methods: ['GET'])]
    public function sysInfo(
        KernelInterface $kernel
    ): Response {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(['command' => 'about',]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $this->render(
            'admin/sysinfo.html.twig',
            [
                'info' => $output->fetch(),
            ]
        );
    }
}
