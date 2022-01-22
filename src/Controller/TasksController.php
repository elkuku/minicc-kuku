<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use function dirname;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
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
        $input = new ArrayInput(
            [
                'command' => 'about',
            ]
        );
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $this->render(
            'admin/sysinfo.html.twig',
            [
                'info' => $output->fetch(),
            ]
        );
    }
    #[Route(path: '/export-table/{name}', name: 'export-table', methods: ['GET'])]
    public function export(
        string $name,
        ManagerRegistry $managerRegistry,
    ): Response {
        $content = json_encode($this->getTableData($name, $managerRegistry));
        $filename = sprintf('export-%s-%s.json', $name, date('Y-m-d'));

        return new Response(
            $content,
            200,
            [
                'Content-Type'        => 'application/txt',
                'Content-Disposition' => sprintf(
                    'attachment; filename="%s"',
                    $filename
                ),
            ]
        );
    }

    #[Route(path: '/import-table', name: 'import-table', methods: ['POST'])]
    public function import(
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $file = $request->files->get('file');
        if (!$file) {
            $this->addFlash('danger', 'No file received.');

            return $this->redirectToRoute('admin-tasks');
        }
        $path = $file->getRealPath();
        if (!$path) {
            $this->addFlash('danger', 'Invalid file.');

            return $this->redirectToRoute('admin-tasks');
        }
        $parts = explode('-', $file->getClientOriginalName());
        if (count($parts) < 2) {
            $this->addFlash(
                'danger',
                'Invalid filename should be "export-{TABLE_NAME}-{DATE}.json".'
            );

            return $this->redirectToRoute('admin-tasks');
        }
        $tableName = $parts[1];
        $newData = json_decode(file_get_contents($path));
        $oldData = $this->getTableData($tableName, $managerRegistry);
        foreach ($newData as $i => $newItem) {
            foreach ($oldData as $oldItem) {
                if ($oldItem['id'] === $newItem->id) {
                    foreach ($newItem as $prop => $value) {
                        if ($oldItem[$prop] !== $value) {
                            throw new UnexpectedValueException(
                                'Data inconsistency.'
                            );
                        }
                    }

                    unset($newData[$i]);
                    continue 2;
                }
            }
        }
        if (!count($newData)) {
            $this->addFlash('success', 'Everything is in Sync :)');

            return $this->redirectToRoute('admin-tasks');
        }
        $queryLines = [];
        $queryLines[] = "INSERT INTO $tableName\n";
        $keys = [];
        foreach (reset($newData) as $prop => $value) {
            $keys[] = $prop;
        }
        $queryLines[] = '('.implode(', ', $keys).")\n";
        $queryLines[] = "VALUES\n";
        $values = [];
        foreach ($newData as $item) {
            $valueLine = '';

            foreach ($item as $value) {
                if (null === $value) {
                    $valueLine .= 'null, ';
                } elseif (strpos($value, '-') || strpos($value, '.')) {
                    $valueLine .= "'$value', ";
                } else {
                    $valueLine .= $value.', ';
                }
            }

            $values[] = sprintf('(%s)', trim($valueLine, ', '));
        }
        $queryLines[] = implode(",\n", $values).';';
        $query = implode('', $queryLines);
        /** @type EntityManager $em */
        $em = $managerRegistry->getManager();
        $statement = $em->getConnection()->prepare($query);
        $statement->executeQuery();
        $this->addFlash('success', count($newData).' lines inserted');

        return $this->redirectToRoute('admin-tasks');
    }

    private function getTableData(string $tableName, ManagerRegistry $managerRegistry,): array|RedirectResponse
    {
        try {
            $query = "SELECT * FROM $tableName;";

            $statement = $managerRegistry->getConnection()->prepare($query);
            $statement->execute();

            $result = $statement->fetchAll();
        } catch (Exception) {
            $this->addFlash('danger', 'There was an error...');

            return $this->redirectToRoute('admin-tasks');
        }

        return $result;
    }
}
