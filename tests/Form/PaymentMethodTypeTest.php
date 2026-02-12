<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\PaymentMethodType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodTypeTest extends TestCase
{
    public function testBuildFormAddsNameField(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('add')
            ->with('name')
            ->willReturnSelf();

        $type = new PaymentMethodType();
        $type->buildForm($builder, []);
    }
}
