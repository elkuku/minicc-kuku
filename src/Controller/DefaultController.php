<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\TaxService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index(StoreRepository $storeRepository, TransactionRepository $transactionRepository, TaxService $taxService): Response
    {
        $user = $this->getUser();
        $balances = null;
        $chartData = [
            'headers'    => [],
            'monthsDebt' => [],
            'balances'   => [],
        ];

        if ($user) {
            foreach ($storeRepository->getActive() as $store) {
                $balance = $transactionRepository->getSaldo($store);
                $chartData['headers'][] = 'Local '.$store->getId();
                $valAlq = $taxService->getValueConTax($store->getValAlq());

                $chartData['monthsDebt'][] = $valAlq ? round(
                    -$balance / $valAlq, 1
                ) : 0;
                $chartData['balances'][] = -$balance;

                $s = new \stdClass();
                $s->amount = $balance;
                $s->store = $store;

                $balances[] = $s;
            }
        }

        return $this->render(
            'default/index.html.twig',
            [
                'stores'    => $user ? $user->getStores() : null,
                'balances'  => $balances,
                'chartData' => [
                    'headers'    => json_encode($chartData['headers']),
                    'monthsDebt' => json_encode($chartData['monthsDebt']),
                    'balances'   => json_encode($chartData['balances']),
                ],
            ]
        );
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(): Response
    {
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }
}
