<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Form;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TransactionType
 */
class TransactionTypeType extends AbstractType
{
    public function __construct(private StoreRepository $storeRepository)
    {
    }
    /**
     * {@inheritdoc}
     */
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
                    'class'        => 'App:TransactionType',
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'store',
                EntityType::class,
                [
                    'class'        => 'App:Store',
                    // 'choice_label' => 'id',
                    // 'choices' => $this->storeRepository->getActive(),
                    'choice_label' => function (Store $store) {
                        return $store->getId().' - '.$store->getDestination();
                    },
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
