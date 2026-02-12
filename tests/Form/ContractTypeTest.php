<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Contract;
use App\Form\ContractType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContractTypeTest extends TestCase
{
    public function testBuildFormAddsExpectedFields(): void
    {
        $expectedFields = [
            'date',
            'storeNumber',
            'destination',
            'valAlq',
            'valGarantia',
            'gender',
            'inqNombreApellido',
            'inqCi',
            'cntLanfort',
            'cntNeon',
            'cntSwitch',
            'cntToma',
            'cntVentana',
            'cntLlaves',
            'cntMedElec',
            'cntMedAgua',
            'medElectrico',
            'medAgua',
            'text',
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

        $type = new ContractType();
        $type->buildForm($builder, []);

        $this->assertSame($expectedFields, $addedFields);
    }

    public function testConfigureOptionsSetsDataClass(): void
    {
        $resolver = new OptionsResolver();

        $type = new ContractType();
        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertSame(Contract::class, $options['data_class']);
    }
}
