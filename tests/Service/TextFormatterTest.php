<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\TextFormatter;
use App\Type\Gender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TextFormatterTest extends TestCase
{
    private TextFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new TextFormatter();
    }

    public function testFormatRuc13Characters(): void
    {
        $user = $this->createUser(ruc: '1234567890001');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456 789 0 001', $result);
    }

    public function testFormatRuc10Characters(): void
    {
        $user = $this->createUser(ruc: '1234567890');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456 789 0', $result);
    }

    public function testFormatRucShorterThan13Characters(): void
    {
        $user = $this->createUser(ruc: '123456789');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456 789', $result);
    }

    public function testFormatRucFallsBackToCi(): void
    {
        $user = $this->createUser(ci: '123456789-0');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456 789 0', $result);
    }

    public function testFormatRucRemovesDashesFromCi(): void
    {
        $user = $this->createUser(ci: '12-34-56');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456', $result);
    }

    public function testFormatRucReturnsQuestionMarkWhenEmpty(): void
    {
        $user = $this->createUser();

        $result = $this->formatter->formatRUC($user);

        self::assertSame('?', $result);
    }

    public function testFormatRucPrefersRucOverCi(): void
    {
        $user = $this->createUser(ruc: '1234567890001', ci: '999999999-9');

        $result = $this->formatter->formatRUC($user);

        self::assertSame('123 456 789 0 001', $result);
    }

    #[DataProvider('rucFormattingProvider')]
    public function testFormatRucVariousInputs(?string $ruc, string $ci, string $expected): void
    {
        $user = $this->createUser(ruc: $ruc, ci: $ci);

        $result = $this->formatter->formatRUC($user);

        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{?string, string, string}>
     */
    public static function rucFormattingProvider(): array
    {
        return [
            '13-digit RUC' => ['1234567890001', '', '123 456 789 0 001'],
            '10-digit RUC' => ['1234567890', '', '123 456 789 0'],
            '9-digit RUC' => ['123456789', '', '123 456 789'],
            '6-digit RUC' => ['123456', '', '123 456'],
            'CI with dash' => [null, '123456789-0', '123 456 789 0'],
            'CI without dash' => [null, '1234567890', '123 456 789 0'],
            'empty both' => [null, '', '?'],
            'empty string RUC' => ['', '', '?'],
        ];
    }

    private function createUser(?string $ruc = null, string $ci = ''): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setGender(Gender::male);
        $user->setInqCi($ci);
        $user->setInqRuc($ruc);

        return $user;
    }
}
