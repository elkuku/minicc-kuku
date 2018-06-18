<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Repository\TransactionRepository;
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
     */
    public function index(TransactionRepository $transactionRepository): Response
    {
	    $saldos = null;
	    $stores = null;

        $user = $this->getUser();

        if ($user) {
            $stores = $user->getStores();
            $saldos = $transactionRepository->getSaldos();
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
     */
    public function about(): Response
    {
        return $this->render('default/about.html.twig', ['user' => $this->getUser()]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }
}
