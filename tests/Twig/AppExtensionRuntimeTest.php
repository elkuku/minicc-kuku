<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ShaFinder;
use App\Service\TaxService;
use App\Twig\Runtime\AppExtensionRuntime;
use PHPUnit\Framework\TestCase;

final class AppExtensionRuntimeTest extends TestCase
{
    public function testGetSystemUsers(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $userRepository = $this->createStub(UserRepository::class);
        $userRepository->method('findActiveUsers')
            ->willReturn([$user]);

        $runtime = new AppExtensionRuntime(
            $userRepository,
            $this->createStub(ShaFinder::class),
            new TaxService(12.0),
        );

        $users = $runtime->getSystemUsers();

        self::assertCount(1, $users);
        self::assertSame($user, $users[0]);
    }

    public function testGetValueWithTax(): void
    {
        $runtime = new AppExtensionRuntime(
            $this->createStub(UserRepository::class),
            $this->createStub(ShaFinder::class),
            new TaxService(12.0),
        );

        $result = $runtime->getValueWithTax(100.0);

        self::assertSame(112.0, $result);
    }

    public function testGetTaxFromTotal(): void
    {
        $runtime = new AppExtensionRuntime(
            $this->createStub(UserRepository::class),
            $this->createStub(ShaFinder::class),
            new TaxService(12.0),
        );

        $result = $runtime->getTaxFromTotal(112.0);

        self::assertEqualsWithDelta(12.0, $result, 0.01);
    }

    public function testGetSHA(): void
    {
        $shaFinder = $this->createStub(ShaFinder::class);
        $shaFinder->method('getSha')
            ->willReturn('abc1234');

        $runtime = new AppExtensionRuntime(
            $this->createStub(UserRepository::class),
            $shaFinder,
            new TaxService(12.0),
        );

        self::assertSame('abc1234', $runtime->getSHA());
    }
}
