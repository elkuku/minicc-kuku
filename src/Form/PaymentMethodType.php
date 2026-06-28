<?php

declare(strict_types=1);

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @see \App\Tests\Form\PaymentMethodTypeTest
 */
class PaymentMethodType extends AbstractType
{
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder->add('name');
    }
}
