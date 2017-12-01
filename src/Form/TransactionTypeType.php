<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TransactionType
 */
class TransactionTypeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date',
                null,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'attr'   => [
                        'class' => 'js-datepicker',
                    ],
                ]
            )
            ->add(
                'type',
                EntityType::class,
                [
                    'class'        => 'App:TransactionType',
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'store',
                EntityType::class,
                [
                    'class'        => 'App:Store',
                    'choice_label' => 'id',
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'class'        => 'App:User',
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'method',
                EntityType::class,
                [
                    'class'        => 'App:PaymentMethod',
                    'choice_label' => 'name',
                ]
            )
            ->add('amount')
            ->add('document')
            ->add('depId', null, ['label' => 'DepositoId'])
            ->add('recipeNo', null, ['label' => 'Factura']);
    }
}
