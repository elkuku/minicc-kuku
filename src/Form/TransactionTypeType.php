<?php

declare(strict_types=1);

namespace App\Form;

use Override;
use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\User;
use App\Type\TransactionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;

class TransactionTypeType extends AbstractType
{
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
    ): void
    {
        $builder
            ->add(
                'date',
                null,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                ]
            )
            ->add(
                'type',
                EnumType::class,
                [
                    'class' => TransactionType::class,
                    'choice_label' => fn(
                        TransactionType $choice
                    ): TranslatableMessage => new TranslatableMessage(
                        $choice->translationKey()
                    ),
                ]
            )
            ->add(
                'store',
                EntityType::class,
                [
                    'class' => Store::class,
                    'choice_label' => fn(Store $store): string => $store->getId() . ' - ' . $store->getDestination(),
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'class' => User::class,
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'method',
                EntityType::class,
                [
                    'class' => PaymentMethod::class,
                    'choice_label' => 'name',
                ]
            )
            ->add('amount', MoneyType::class, ['currency' => 'usd'])
            ->add('document')
            ->add('comment')
            ->add('depId', null, [
                'label' => 'DepositoId',
            ])
            ->add('recipeNo', null, [
                'label' => 'Factura',
            ]);
    }
}
