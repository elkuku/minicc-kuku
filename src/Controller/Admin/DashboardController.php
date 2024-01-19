<?php

namespace App\Controller\Admin;

use App\Entity\Contract;
use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\User;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    #[Route('/admin', name: 'admin', methods: ['GET', 'POST'])]
    #[IsGranted(User::ROLES['admin'])]
    public function index(): Response
    {
        $stores = $this->storeRepository->findAll();
        $balances = [];

        foreach ($stores as $store) {
            $balances[$store->getId()] = $this->transactionRepository
                ->getSaldo($store);
        }

        return $this->render(
            'easyadmin/index.html.twig',
            [
                'stores' => $stores,
                'balances' => $balances,
            ]
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Minicc Kuku DB');
    }

    public function configureMenuItems(): \Iterator
    {
        yield MenuItem::linkToCrud(
            'Stores',
            'fa fa-question-circle',
            Store::class
        );
        yield MenuItem::linkToCrud(
            'Users',
            'fa fa-question-circle',
            User::class
        );
        // ->setQueryParameter('filters[isActive][comparison]', '=')
        // ->setQueryParameter('filters[isActive][value]', 1);
        yield MenuItem::linkToCrud(
            'Contracts',
            'fa fa-question-circle',
            Contract::class
        );
        yield MenuItem::linkToCrud(
            'Payment Methods',
            'fa fa-question-circle',
            PaymentMethod::class
        );

        yield MenuItem::section();
        yield MenuItem::linkToUrl(
            'Homepage',
            'fas fa-home',
            $this->generateUrl('welcome')
        );
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }
}
