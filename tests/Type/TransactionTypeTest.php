<?php

declare(strict_types=1);

namespace App\Tests\Type;

use Iterator;
use App\Type\TransactionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;

final class TransactionTypeTest extends TestCase
{
    #[DataProvider('translationKeyProvider')]
    public function testTranslationKey(TransactionType $type, string $expected): void
    {
        $this->assertSame($expected, $type->translationKey());
    }

    /**
     * @return Iterator<string, array{TransactionType, string}>
     */
    public static function translationKeyProvider(): Iterator
    {
        yield 'rent' => [TransactionType::rent, 'TRANSACTION_TYPE_RENT'];
        yield 'payment' => [TransactionType::payment, 'TRANSACTION_TYPE_PAYMENT'];
        yield 'initial' => [TransactionType::initial, 'TRANSACTION_TYPE_INITIAL'];
        yield 'adjustment' => [TransactionType::adjustment, 'TRANSACTION_TYPE_ADJUSTMENT'];
    }

    #[DataProvider('translatedNameProvider')]
    public function testTranslatedName(TransactionType $type, string $expectedKey): void
    {
        $translatable = $type->translatedName();

        $this->assertInstanceOf(TranslatableMessage::class, $translatable);
        $this->assertSame($expectedKey, $translatable->getMessage());
    }

    /**
     * @return Iterator<string, array{TransactionType, string}>
     */
    public static function translatedNameProvider(): Iterator
    {
        yield 'rent' => [TransactionType::rent, 'TRANSACTION_TYPE_RENT'];
        yield 'payment' => [TransactionType::payment, 'TRANSACTION_TYPE_PAYMENT'];
        yield 'initial' => [TransactionType::initial, 'TRANSACTION_TYPE_INITIAL'];
        yield 'adjustment' => [TransactionType::adjustment, 'TRANSACTION_TYPE_ADJUSTMENT'];
    }

    #[DataProvider('cssClassProvider')]
    public function testCssClass(TransactionType $type, string $expected): void
    {
        $this->assertSame($expected, $type->cssClass());
    }

    /**
     * @return Iterator<string, array{TransactionType, string}>
     */
    public static function cssClassProvider(): Iterator
    {
        yield 'rent' => [TransactionType::rent, 'table-success'];
        yield 'payment' => [TransactionType::payment, ''];
        yield 'initial' => [TransactionType::initial, 'table-info'];
        yield 'adjustment' => [TransactionType::adjustment, 'table-warning'];
    }

    #[DataProvider('cssClassPdfProvider')]
    public function testCssClassPdf(TransactionType $type, string $expected): void
    {
        $this->assertSame($expected, $type->cssClassPdf());
    }

    /**
     * @return Iterator<string, array{TransactionType, string}>
     */
    public static function cssClassPdfProvider(): Iterator
    {
        yield 'rent' => [TransactionType::rent, 'rent'];
        yield 'payment' => [TransactionType::payment, ''];
        yield 'initial' => [TransactionType::initial, 'initial'];
        yield 'adjustment' => [TransactionType::adjustment, 'adjustment'];
    }

    public function testEnumCasesCount(): void
    {
        $this->assertCount(4, TransactionType::cases());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('1', TransactionType::rent->value);
        $this->assertSame('2', TransactionType::payment->value);
        $this->assertSame('3', TransactionType::initial->value);
        $this->assertSame('4', TransactionType::adjustment->value);
    }
}
