<?php

declare(strict_types=1);

namespace App\Tests\Type;

use App\Type\Gender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GenderTest extends TestCase
{
    public function testGetChoicesReturnsExpectedArray(): void
    {
        $expected = [
            'male' => '1',
            'female' => '2',
            'other' => '3',
        ];

        self::assertSame($expected, Gender::getChoices());
    }

    #[DataProvider('titleProvider')]
    public function testTitle(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->title());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function titleProvider(): array
    {
        return [
            'male' => [Gender::male, 'Sr.'],
            'female' => [Gender::female, 'Sra.'],
            'other' => [Gender::other, 'Sr@.'],
        ];
    }

    #[DataProvider('titleLongProvider')]
    public function testTitleLong(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->titleLong());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function titleLongProvider(): array
    {
        return [
            'male' => [Gender::male, 'señor'],
            'female' => [Gender::female, 'señora'],
            'other' => [Gender::other, 'señor@'],
        ];
    }

    #[DataProvider('salutationProvider')]
    public function testSalutation(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->salutation());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function salutationProvider(): array
    {
        return [
            'male' => [Gender::male, 'Estimado'],
            'female' => [Gender::female, 'Estimada'],
            'other' => [Gender::other, 'Estimad@'],
        ];
    }

    #[DataProvider('text1Provider')]
    public function testText1(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->text_1());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function text1Provider(): array
    {
        return [
            'male' => [Gender::male, 'el'],
            'female' => [Gender::female, 'la'],
            'other' => [Gender::other, 'l@'],
        ];
    }

    #[DataProvider('text2Provider')]
    public function testText2(Gender $gender, string $expected): void
    {
        self::assertSame($expected, $gender->text_2());
    }

    /**
     * @return array<string, array{Gender, string}>
     */
    public static function text2Provider(): array
    {
        return [
            'male' => [Gender::male, 'del'],
            'female' => [Gender::female, 'de la'],
            'other' => [Gender::other, 'de l@'],
        ];
    }

    public function testEnumCasesCount(): void
    {
        self::assertCount(3, Gender::cases());
    }

    public function testEnumValues(): void
    {
        self::assertSame('1', Gender::male->value);
        self::assertSame('2', Gender::female->value);
        self::assertSame('3', Gender::other->value);
    }
}
