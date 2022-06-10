<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Form;

use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\TransactionType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TransactionType
 */
class TransactionTypeType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
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
                EntityType::class,
                [
                    'class'        => TransactionType::class,
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'store',
                EntityType::class,
                [
                    'class'        => Store::class,
                    'choice_label' => fn(Store $store): string => $store->getId(
                        ).' - '.$store->getDestination(),
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'class'        => User::class,
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'method',
                EntityType::class,
                [
                    'class'        => PaymentMethod::class,
                    'choice_label' => 'name',
                ]
            )
            ->add('amount', MoneyType::class)
            ->add('document')
            ->add('depId', null, ['label' => 'DepositoId'])
            ->add('recipeNo', null, ['label' => 'Factura']);
    }
}
