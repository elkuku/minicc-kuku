<?php

declare(strict_types=1);

namespace App\Tests\Type;

use Iterator;
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

        $this->assertSame($expected, Gender::getChoices());
    }

    #[DataProvider('titleProvider')]
    public function testTitle(Gender $gender, string $expected): void
    {
        $this->assertSame($expected, $gender->title());
    }

    /**
     * @return Iterator<string, array{Gender, string}>
     */
    public static function titleProvider(): Iterator
    {
        yield 'male' => [Gender::male, 'Sr.'];
        yield 'female' => [Gender::female, 'Sra.'];
        yield 'other' => [Gender::other, 'Sr@.'];
    }

    #[DataProvider('titleLongProvider')]
    public function testTitleLong(Gender $gender, string $expected): void
    {
        $this->assertSame($expected, $gender->titleLong());
    }

    /**
     * @return Iterator<string, array{Gender, string}>
     */
    public static function titleLongProvider(): Iterator
    {
        yield 'male' => [Gender::male, 'señor'];
        yield 'female' => [Gender::female, 'señora'];
        yield 'other' => [Gender::other, 'señor@'];
    }

    #[DataProvider('salutationProvider')]
    public function testSalutation(Gender $gender, string $expected): void
    {
        $this->assertSame($expected, $gender->salutation());
    }

    /**
     * @return Iterator<string, array{Gender, string}>
     */
    public static function salutationProvider(): Iterator
    {
        yield 'male' => [Gender::male, 'Estimado'];
        yield 'female' => [Gender::female, 'Estimada'];
        yield 'other' => [Gender::other, 'Estimad@'];
    }

    #[DataProvider('text1Provider')]
    public function testText1(Gender $gender, string $expected): void
    {
        $this->assertSame($expected, $gender->text_1());
    }

    /**
     * @return Iterator<string, array{Gender, string}>
     */
    public static function text1Provider(): Iterator
    {
        yield 'male' => [Gender::male, 'el'];
        yield 'female' => [Gender::female, 'la'];
        yield 'other' => [Gender::other, 'l@'];
    }

    #[DataProvider('text2Provider')]
    public function testText2(Gender $gender, string $expected): void
    {
        $this->assertSame($expected, $gender->text_2());
    }

    /**
     * @return Iterator<string, array{Gender, string}>
     */
    public static function text2Provider(): Iterator
    {
        yield 'male' => [Gender::male, 'del'];
        yield 'female' => [Gender::female, 'de la'];
        yield 'other' => [Gender::other, 'de l@'];
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, Gender::cases());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('1', Gender::male->value);
        $this->assertSame('2', Gender::female->value);
        $this->assertSame('3', Gender::other->value);
    }
}
