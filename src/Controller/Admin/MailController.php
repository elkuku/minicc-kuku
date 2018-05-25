<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MailController
 */
class MailController extends Controller
{
	/**
	 * @Route("/test-mail", name="test-mail")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @return Response
	 */
	public function testMailAction()
	{
		$fileContents = 'FileContents hahaha =;)';

		$root = realpath($this->get('kernel')->getRootDir() . '/..');

		$fileName = $root . '/tests/testmail.txt';

		file_put_contents($fileName, $fileContents);

		$name    = 'kuku';
		$message = \Swift_Message::newInstance()
			->setSubject('Hello Email')
			->setFrom('minicckuku@gmail.com')
			->setTo('minicckuku@gmail.com')
			->setBody(
				$this->renderView(
					'email/registration.html.twig',
					array('name' => $name)
				),
				'text/html'
			)
			->attach(\Swift_Attachment::fromPath($fileName));

		$count = $this->get('mailer')->send($message);

		if (!$count)
		{
			$this->addFlash('danger', 'There was an error sending mail...');
		}
		else
		{
			$this->addFlash('success', ($count > 1 ? $count . ' mails have been sent.' : 'One mail has been sent.'));
		}

		return $this->render('default/index.html.twig');
	}
}
