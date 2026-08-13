<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRole;
use App\Type\Gender;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see \App\Tests\Form\UserFullTypeTest
 */
class UserFullType extends AbstractType
{
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add('isActive')
            ->add('role', EnumType::class, [
                'class' => UserRole::class,
                'choice_label' => fn(UserRole $role): string => $role->label(),
                // A missing/unmatched submission must not silently escalate
                // privilege - fall back to the least-privileged role rather
                // than crashing (setRole() takes a non-nullable UserRole).
                'empty_data' => UserRole::USER->value,
            ])
            ->add('gender', EnumType::class, [
                'class' => Gender::class,
                'empty_data' => Gender::other->value,
            ])
            ->add('name', TextType::class, [
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'empty_data' => '',
            ])
            ->add('inqCi', null, [
                'empty_data' => '',
            ])
            ->add('inqRuc', null, [
                'required' => false,
            ])
            ->add('telefono', null, [
                'required' => false,
            ])
            ->add('telefono2', null, [
                'required' => false,
            ])
            ->add('direccion', null, [
                'required' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
