<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40.
 */
namespace App\Form;

use App\Entity\Contract;
use App\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Contract::class,
            ]
        );
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
    ): void
    {
        $builder
            ->add('date')
            // Store
            ->add('storeNumber')
            ->add('destination', null, [
                'label' => 'Destino',
            ])
            ->add('valAlq', null, [
                'label' => 'Alquiler',
            ])
            ->add('valGarantia')
            // User
            ->add('gender', EnumType::class, [
                'class' => Gender::class,
            ])
            ->add('inqNombreApellido', null, ['empty_data' => ''])
            ->add('inqCi')
            // Accesories
            ->add('cntLanfort', null, [
                'label' => 'Lanfort',
            ])
            ->add('cntNeon', null, [
                'label' => 'Neon',
            ])
            ->add('cntSwitch', null, [
                'label' => 'Switch',
            ])
            ->add('cntToma', null, [
                'label' => 'Toma',
            ])
            ->add('cntVentana', null, [
                'label' => 'Ventana',
            ])
            ->add('cntLlaves', null, [
                'label' => 'Llaves',
            ])
            ->add('cntMedElec', null, [
                'label' => 'Medidor',
            ])
            ->add('cntMedAgua', null, [
                'label' => 'Medidor',
            ])
            ->add('medElectrico', null, [
                'label' => 'Electrico',
            ])
            ->add('medAgua', null, [
                'label' => 'Agua',
            ])
            // Text
            ->add('text');
    }
}
