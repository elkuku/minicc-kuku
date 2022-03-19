<?php

namespace App\Controller\Admin;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Service\TaxService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

class ContractCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TaxService $taxService,
        private readonly ContractRepository $contractRepository
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Contract::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('storeNumber'),
            Field::new('date'),
            Field::new('inqNombreapellido'),
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
                ]),
            TextEditorField::new('text')
                ->hideOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateContract = Action::new('generateContract', 'Generate')
            ->linkToRoute(
                'contract-generate',
                function (Contract $contract): array {
                    return [
                        'id' => $contract->getId(),
                    ];
                }
            );

        $actions->add(Crud::PAGE_INDEX, $generateContract);

        return parent::configureActions($actions);
    }

    public function createEntity(string $entityFqcn): Contract
    {
        $plantilla = $this->contractRepository->findPlantilla();
        $contract = new Contract;
        $contract->setText($plantilla->getText());

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
}
