<?php

namespace App\Controller\Admin;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use App\Service\TaxService;
use App\Type\Gender;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\HttpFoundation\Request;

class ContractCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TaxService $taxService,
        private readonly ContractRepository $contractRepository,
        private readonly StoreRepository $storeRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Contract::class;
    }

    public function configureResponseParameters(
        KeyValueStore $responseParameters
    ): KeyValueStore {
        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            $responseParameters->set(
                'stores',
                $this->storeRepository->getActive()
            );
            $responseParameters->set(
                'users',
                $this->userRepository->findActiveUsers()
            );
        }

        return $responseParameters;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('storeNumber'),
            Field::new('date'),
            // AssociationField::new('gender')->onlyOnForms(),
            ChoiceField::new('gender')
                ->setChoices(Gender::getChoices())
                ->onlyOnForms(),
            Field::new('inqNombreApellido'),
            Field::new('inqCi')
                ->onlyOnForms(),
            NumberField::new('valAlq')
                ->setFormTypeOptions([
                    'row_attr' => [
                        'data-controller' => 'taxcalc2',
                        'data-taxcalc2-taxrate-value' => $this->taxService->getTaxValue(
                        ),
                    ],
                    'attr'     => [
                        'data-taxcalc2-target' => 'withoutTax',
                        'data-action'          => 'taxcalc2#calcWithTax',
                    ],
                ])
                ->onlyOnForms(),
            TextEditorField::new('text')
                ->hideOnIndex(),
            Field::new('destination')->onlyOnForms(),
            Field::new('valGarantia')->onlyOnForms(),
            Field::new('cntLanfort')->onlyOnForms(),
            Field::new('cntNeon')->onlyOnForms(),
            Field::new('cntSwitch')->onlyOnForms(),
            Field::new('cntToma')->onlyOnForms(),
            Field::new('cntVentana')->onlyOnForms(),
            Field::new('cntLlaves')->onlyOnForms(),
            Field::new('cntMedElec')->onlyOnForms(),
            Field::new('medElectrico')->onlyOnForms(),
            Field::new('cntMedAgua')->onlyOnForms(),
            Field::new('medAgua')->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateContract = Action::new('generateContract', 'Generate')
            ->linkToRoute(
                'contract-generate',
                fn(Contract $contract): array => [
                    'id' => $contract->getId(),
                ]
            );

        $actions->add(Crud::PAGE_INDEX, $generateContract);

        return parent::configureActions($actions);
    }

    public function createEntity(string $entityFqcn): Contract
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $contract = new Contract;

        if ($request instanceof Request) {
            $store = $this->storeRepository
                ->find($request->request->getInt('store'));
            $user = $this->userRepository
                ->find($request->request->getInt('user'));

            if ($store) {
                $contract->setValuesFromStore($store);
            }

            if ($user) {
                $contract
                    ->setInqNombreapellido((string)$user->getName())
                    ->setInqCi($user->getInqCi())
                    ->setGender($user->getGender());
            }
        }

        $contract->setText(
            $this->contractRepository->findTemplate()->getText()
        );

        return $contract;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        )
            ->andWhere('entity.id > 1');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('storeNumber')
            ->add('date');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->overrideTemplates([
            'crud/index' => 'easyadmin/crud/contract/index.html.twig',
            'crud/new'   => 'easyadmin/crud/contract/new.html.twig',
            'crud/edit'  => 'easyadmin/crud/contract/edit.html.twig',
        ]);
    }
}
