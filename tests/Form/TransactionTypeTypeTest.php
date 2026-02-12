<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\TransactionTypeType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class TransactionTypeTypeTest extends TestCase
{
    public function testBuildFormAddsExpectedFields(): void
    {
        $expectedFields = [
            'date',
            'type',
            'store',
            'user',
            'method',
            'amount',
            'document',
            'comment',
            'depId',
            'recipeNo',
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

        $type = new TransactionTypeType();
        $type->buildForm($builder, []);

        $this->assertSame($expectedFields, $addedFields);
    }
}
