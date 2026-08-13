<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Contract;
use App\Type\Gender;
use DateTime;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see \App\Tests\Form\ContractTypeTest
 */
class ContractType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Contract::class,
            ]
        );
    }

    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add('date', null, [
                // A blank date has no neutral fallback like '' or 0; default
                // to "today" rather than crashing (Contract::setDate() takes
                // a non-nullable DateTime).
                'empty_data' => static fn (): string => new DateTime()->format('Y-m-d'),
            ])
            // Store
            ->add('storeNumber', null, [
                'empty_data' => '0',
            ])
            ->add('destination', null, [
                'label' => 'Destino',
                'empty_data' => '',
            ])
            ->add('valAlq', null, [
                'label' => 'Alquiler',
                'empty_data' => '0',
            ])
            ->add('valGarantia', null, [
                'empty_data' => '0',
            ])
            // User
            ->add('gender', EnumType::class, [
                'class' => Gender::class,
            ])
            ->add('inqNombreApellido', null, ['empty_data' => ''])
            ->add('inqCi', null, [
                'empty_data' => '',
            ])
            // Accesories
            ->add('cntLanfort', null, [
                'label' => 'Lanfort',
                'empty_data' => '0',
            ])
            ->add('cntNeon', null, [
                'label' => 'Neon',
                'empty_data' => '0',
            ])
            ->add('cntSwitch', null, [
                'label' => 'Switch',
                'empty_data' => '0',
            ])
            ->add('cntToma', null, [
                'label' => 'Toma',
                'empty_data' => '0',
            ])
            ->add('cntVentana', null, [
                'label' => 'Ventana',
                'empty_data' => '0',
            ])
            ->add('cntLlaves', null, [
                'label' => 'Llaves',
                'empty_data' => '0',
            ])
            ->add('cntMedElec', null, [
                'label' => 'Medidor',
                'empty_data' => '0',
            ])
            ->add('cntMedAgua', null, [
                'label' => 'Medidor',
                'empty_data' => '0',
            ])
            ->add('medElectrico', null, [
                'label' => 'Electrico',
                'empty_data' => '',
            ])
            ->add('medAgua', null, [
                'label' => 'Agua',
                'empty_data' => '',
            ])
            // Text
            ->add('text', null, [
                'empty_data' => '',
            ]);
    }
}
