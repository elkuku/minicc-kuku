<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserFullType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserFullTypeTest extends TestCase
{
    public function testBuildFormAddsExpectedFields(): void
    {
        $expectedFields = [
            'isActive',
            'gender',
            'name',
            'email',
            'inqCi',
            'inqRuc',
            'telefono',
            'telefono2',
            'direccion',
        ];

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();

        $addedFields = [];
        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add')
            ->willReturnCallback(function (string $name) use (&$addedFields, $builder): FormBuilderInterface {
                $addedFields[] = $name;

                return $builder;
            });

        $type = new UserFullType();
        $type->buildForm($builder, []);

        $this->assertSame($expectedFields, $addedFields);
    }

    public function testConfigureOptionsSetsDataClass(): void
    {
        $resolver = new OptionsResolver();

        $type = new UserFullType();
        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertSame(User::class, $options['data_class']);
    }
}
