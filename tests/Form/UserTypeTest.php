<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserTypeTest extends TestCase
{
    public function testBuildFormAddsNameAndEmail(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();

        $addedFields = [];
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(function (string $name, ?string $type = null) use (&$addedFields, $builder): FormBuilderInterface {
                $addedFields[$name] = $type;

                return $builder;
            });

        $type = new UserType();
        $type->buildForm($builder, []);

        $this->assertArrayHasKey('name', $addedFields);
        $this->assertSame(TextType::class, $addedFields['name']);
        $this->assertArrayHasKey('email', $addedFields);
        $this->assertSame(EmailType::class, $addedFields['email']);
    }

    public function testConfigureOptionsSetsDataClass(): void
    {
        $resolver = new OptionsResolver();

        $type = new UserType();
        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertSame(User::class, $options['data_class']);
    }
}
