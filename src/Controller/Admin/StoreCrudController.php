<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Service\TaxService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StoreCrudController extends AbstractCrudController
{
    public function __construct(private readonly TaxService $taxService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Store::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();
            // ->setFormTypeOption('disabled', true);
        yield TextField::new('destination');
        yield AssociationField::new('user')
            ->autocomplete()
            ->setQueryBuilder(function (QueryBuilder $qb) {
                $qb->andWhere('entity.state = :state')
                    ->setParameter('state', 1);
            });;
        yield NumberField::new('valAlq')
            ->setFormTypeOptions([
                'row_attr' => [
                    'data-controller'             => 'taxcalc2',
                    'data-taxcalc2-taxrate-value' => $this->taxService->getTaxValue(
                    ),
                ],
                'attr'     => [
                    'data-taxcalc2-target' => 'withoutTax',
                    'data-action'          => 'taxcalc2#calcWithTax',
                ],
            ]);
        yield Field::new('cntLanfort', 'Lanfort')->onlyOnForms();
        yield Field::new('cntNeon', 'Neon')->onlyOnForms();
        yield Field::new('cntSwitch', 'Swtches')->onlyOnForms();
        yield Field::new('cntToma', 'Tomacorrientes')->onlyOnForms();
        yield Field::new('cntVentana', 'Ventanas')->onlyOnForms();
        yield Field::new('cntLlaves', 'Llaves')->onlyOnForms();
        yield Field::new('medElectrico', 'Medidor Electrico')->onlyOnForms();
        yield Field::new('medAgua', 'Medidor Agua')->onlyOnForms();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->overrideTemplates([
            'crud/edit' =>            'easyadmin/crud/store/edit.html.twig',
            'crud/new' =>            'easyadmin/crud/store/new.html.twig',

            ]
        );
    }
}
