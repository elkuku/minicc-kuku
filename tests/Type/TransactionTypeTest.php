<?php

declare(strict_types=1);

namespace App\Tests\Type;

use App\Type\TransactionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;

final class TransactionTypeTest extends TestCase
{
    #[DataProvider('translationKeyProvider')]
    public function testTranslationKey(TransactionType $type, string $expected): void
    {
        self::assertSame($expected, $type->translationKey());
    }

    /**
     * @return array<string, array{TransactionType, string}>
     */
    public static function translationKeyProvider(): array
    {
        return [
            'rent' => [TransactionType::rent, 'TRANSACTION_TYPE_RENT'],
            'payment' => [TransactionType::payment, 'TRANSACTION_TYPE_PAYMENT'],
            'initial' => [TransactionType::initial, 'TRANSACTION_TYPE_INITIAL'],
            'adjustment' => [TransactionType::adjustment, 'TRANSACTION_TYPE_ADJUSTMENT'],
        ];
    }

    #[DataProvider('translatedNameProvider')]
    public function testTranslatedName(TransactionType $type, string $expectedKey): void
    {
        $translatable = $type->translatedName();

        self::assertInstanceOf(TranslatableMessage::class, $translatable);
        self::assertSame($expectedKey, $translatable->getMessage());
    }

    /**
     * @return array<string, array{TransactionType, string}>
     */
    public static function translatedNameProvider(): array
    {
        return [
            'rent' => [TransactionType::rent, 'TRANSACTION_TYPE_RENT'],
            'payment' => [TransactionType::payment, 'TRANSACTION_TYPE_PAYMENT'],
            'initial' => [TransactionType::initial, 'TRANSACTION_TYPE_INITIAL'],
            'adjustment' => [TransactionType::adjustment, 'TRANSACTION_TYPE_ADJUSTMENT'],
        ];
    }

    #[DataProvider('cssClassProvider')]
    public function testCssClass(TransactionType $type, string $expected): void
    {
        self::assertSame($expected, $type->cssClass());
    }

    /**
     * @return array<string, array{TransactionType, string}>
     */
    public static function cssClassProvider(): array
    {
        return [
            'rent' => [TransactionType::rent, 'table-success'],
            'payment' => [TransactionType::payment, ''],
            'initial' => [TransactionType::initial, 'table-info'],
            'adjustment' => [TransactionType::adjustment, 'table-warning'],
        ];
    }

    #[DataProvider('cssClassPdfProvider')]
    public function testCssClassPdf(TransactionType $type, string $expected): void
    {
        self::assertSame($expected, $type->cssClassPdf());
    }

    /**
     * @return array<string, array{TransactionType, string}>
     */
    public static function cssClassPdfProvider(): array
    {
        return [
            'rent' => [TransactionType::rent, 'rent'],
            'payment' => [TransactionType::payment, ''],
            'initial' => [TransactionType::initial, 'initial'],
            'adjustment' => [TransactionType::adjustment, 'adjustment'],
        ];
    }

    public function testEnumCasesCount(): void
    {
        self::assertCount(4, TransactionType::cases());
    }

    public function testEnumValues(): void
    {
        self::assertSame('1', TransactionType::rent->value);
        self::assertSame('2', TransactionType::payment->value);
        self::assertSame('3', TransactionType::initial->value);
        self::assertSame('4', TransactionType::adjustment->value);
    }
}
