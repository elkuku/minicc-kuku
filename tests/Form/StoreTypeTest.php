<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\StoreType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class StoreTypeTest extends TestCase
{
    public function testBuildFormAddsExpectedFields(): void
    {
        $expectedFields = [
            'user',
            'destination',
            'valAlq',
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

        $type = new StoreType();
        $type->buildForm($builder, []);

        $this->assertSame($expectedFields, $addedFields);
    }
}
