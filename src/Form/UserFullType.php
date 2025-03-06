<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40.
 */

namespace App\Form;

use App\Entity\User;
use App\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFullType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
    ): void
    {
        $builder
            ->add('isActive')
            ->add('gender', EnumType::class, [
                'class' => Gender::class,
            ])
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('inqCi')
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
