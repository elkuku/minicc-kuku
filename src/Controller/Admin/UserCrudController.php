<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Type\Gender;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): \Iterator
    {
        yield Field::new('isActive');
        yield ChoiceField::new('gender')
            ->setChoices(Gender::getChoices());
        yield TextField::new('name');
        yield TextField::new('inqCi')
            ->hideOnIndex();
        yield EmailField::new('email')
            ->hideOnIndex();
        yield ChoiceField::new('role')
            ->setChoices(User::ROLES)
            ->renderAsBadges()
            ->hideOnIndex();
        yield ArrayField::new('stores')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isActive');
    }
}
