<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ViewController
 */
class ViewController extends Controller
{
	/**
	 * @Route("/console-view/{item}", name="console-view")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param string $item
	 *
	 * @return Response
	 */
	public function consoleViewAction($item)
	{
		$command = [];

		switch ($item)
		{
			case 'routes':
				$command['command'] = 'debug:router';
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
}
