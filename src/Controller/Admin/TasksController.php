<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 26.05.18
 * Time: 10:21
 */

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TasksController
 */
class TasksController extends Controller
{
	/**
	 * @Route("/admin-tasks", name="admin-tasks")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function index(): Response
	{
		return $this->render('admin/tasks.html.twig');
	}

	/**
	 * @Route("/console-view/{item}", name="console-view")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param string  $item
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function consoleView(string $item, Request $request): Response
	{
		$command = [];

		switch ($item)
		{
			case 'routes':
				$command['command'] = 'debug:router';
				break;
			case 'route-match':
				$command['command']   = 'router:match';
				$command['path_info'] = $request->request->get('route');
				break;
			case 'migrations':
				$command['command'] = 'doctrine:migrations:status';
				break;
			case 'security':
				$command['command']  = 'security:check';
				$command['lockfile'] = realpath($this->get('kernel')->getRootDir() . '/..');
				break;
			default:
				throw new \UnexpectedValueException('Unknown command');
		}

		$application = new Application($this->get('kernel'));
		$application->setAutoExit(false);

		$input  = new ArrayInput($command);
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

	/**
	 * @Route("/sysinfo", name="sysinfo")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function sysInfo(): Response
	{
		return $this->render(
			'admin/sysinfo.html.twig',
			[
				'info' => [
					'phpVersion' => PHP_VERSION,
				],
			]
		);
	}
}
