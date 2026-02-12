<?php

declare(strict_types=1);

namespace App\Tests\Service;

use Iterator;
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

        $this->assertSame('123 456 789 0 001', $result);
    }

    public function testFormatRuc10Characters(): void
    {
        $user = $this->createUser(ruc: '1234567890');

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('123 456 789 0', $result);
    }

    public function testFormatRucShorterThan13Characters(): void
    {
        $user = $this->createUser(ruc: '123456789');

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('123 456 789', $result);
    }

    public function testFormatRucFallsBackToCi(): void
    {
        $user = $this->createUser(ci: '123456789-0');

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('123 456 789 0', $result);
    }

    public function testFormatRucRemovesDashesFromCi(): void
    {
        $user = $this->createUser(ci: '12-34-56');

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('123 456', $result);
    }

    public function testFormatRucReturnsQuestionMarkWhenEmpty(): void
    {
        $user = $this->createUser();

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('?', $result);
    }

    public function testFormatRucPrefersRucOverCi(): void
    {
        $user = $this->createUser(ruc: '1234567890001', ci: '999999999-9');

        $result = $this->formatter->formatRUC($user);

        $this->assertSame('123 456 789 0 001', $result);
    }

    #[DataProvider('rucFormattingProvider')]
    public function testFormatRucVariousInputs(?string $ruc, string $ci, string $expected): void
    {
        $user = $this->createUser(ruc: $ruc, ci: $ci);

        $result = $this->formatter->formatRUC($user);

        $this->assertSame($expected, $result);
    }

    /**
     * @return Iterator<string, array{(string | null), string, string}>
     */
    public static function rucFormattingProvider(): Iterator
    {
        yield '13-digit RUC' => ['1234567890001', '', '123 456 789 0 001'];
        yield '10-digit RUC' => ['1234567890', '', '123 456 789 0'];
        yield '9-digit RUC' => ['123456789', '', '123 456 789'];
        yield '6-digit RUC' => ['123456', '', '123 456'];
        yield 'CI with dash' => [null, '123456789-0', '123 456 789 0'];
        yield 'CI without dash' => [null, '1234567890', '123 456 789 0'];
        yield 'empty both' => [null, '', '?'];
        yield 'empty string RUC' => ['', '', '?'];
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
