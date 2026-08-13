<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @see \App\Tests\Form\StoreTypeTest
 */
class StoreType extends AbstractType
{
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add(
                'user',
                EntityType::class,
                [
                    'class' => User::class,
                    'choice_label' => 'name',
                    'placeholder' => '-Desocupado-',
                    'required' => false,
                    'label' => 'Inquilino',
                    'query_builder' => static fn(
                        EntityRepository $er
                    ): QueryBuilder => $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->andWhere('u.isActive = :state')
                        ->setParameter('role', User::ROLES['user'])
                        ->setParameter('state', true)
                        ->orderBy('u.name'),
                ]
            )
            ->add('destination', null, [
                'label' => 'Destino',
                'empty_data' => '',
            ])
            ->add('valAlq', null, [
                'label' => 'Alquiler',
                'empty_data' => '0',
            ])
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
            ]);
    }
}
