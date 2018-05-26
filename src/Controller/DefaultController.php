<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListController
 */
class DefaultController extends Controller
{
	/**
	 * @Route("/", name="welcome")
	 * @return Response
	 */
	public function index(): Response
	{
		$user = $this->getUser();

		if ($user)
		{
			$stores = $user->getStores();
			$saldos = $this->getDoctrine()->getRepository(Transaction::class)->getSaldos();
		}
		else
		{
			$saldos = null;
			$stores = null;
		}

		return $this->render(
			'default/index.html.twig',
			[
				'stores' => $stores,
				'saldos' => $saldos,
			]
		);
	}

	/**
	 * @Route("/about", name="about")
	 * @return Response
	 */
	public function aboutAction(): Response
	{
		return $this->render('default/about.html.twig', ['user' => $this->getUser()]);
	}

	/**
	 * @Route("/contact", name="contact")
	 * @return Response
	 */
	public function contactAction(): Response
	{
		return $this->render('default/contact.html.twig');
	}
}
