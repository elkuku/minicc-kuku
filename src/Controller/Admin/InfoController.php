<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InfoController
 */
class InfoController extends Controller
{
	/**
	 * @Route("/sysinfo", name="sysinfo")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function sysInfoAction()
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
