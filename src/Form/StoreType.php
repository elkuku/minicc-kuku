<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40.
 */

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class StoreType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
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
            ])
            ->add('valAlq', null, [
                'label' => 'Alquiler',
            ])
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
            ]);
    }
}
