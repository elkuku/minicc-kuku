<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Store;
use App\Entity\User;
use App\Service\BulkMailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class BulkMailServiceTest extends TestCase
{
    public function testSendsEmailToEachSelectedStore(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::exactly(2))->method('send');

        $stores = [
            $this->makeStore(1),
            $this->makeStore(2),
            $this->makeStore(3),
        ];
        $recipients = [1 => true, 2 => true];

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores($stores, $recipients, fn() => new Email());

        self::assertCount(2, $result->getSuccesses());
        self::assertContains(1, $result->getSuccesses());
        self::assertContains(2, $result->getSuccesses());
        self::assertFalse($result->hasFailures());
    }

    public function testSkipsStoresNotInRecipients(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $stores = [$this->makeStore(1), $this->makeStore(2)];

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores($stores, [1 => true], fn() => new Email());

        self::assertCount(1, $result->getSuccesses());
        self::assertSame([1], $result->getSuccesses());
    }

    public function testSkipsStoresWithoutUser(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $store = $this->makeStore(1, withUser: false);

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores([$store], [1 => true], fn() => new Email());

        self::assertFalse($result->hasSuccesses());
        self::assertFalse($result->hasFailures());
    }

    public function testCollectsFailuresOnTransportException(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('SMTP error'));

        $stores = [$this->makeStore(1), $this->makeStore(2)];

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores($stores, [1 => true, 2 => true], fn() => new Email());

        self::assertFalse($result->hasSuccesses());
        self::assertTrue($result->hasFailures());
        self::assertCount(2, $result->getFailures());
        self::assertSame('SMTP error', $result->getFailures()[0]);
    }

    public function testEmptyStoresReturnsEmptyResult(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores([], [1 => true], fn() => new Email());

        self::assertFalse($result->hasSuccesses());
        self::assertFalse($result->hasFailures());
    }

    public function testEmptyRecipientsSkipsAllStores(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $stores = [$this->makeStore(1), $this->makeStore(2)];

        $service = new BulkMailService($mailer);
        $result = $service->sendToFilteredStores($stores, [], fn() => new Email());

        self::assertFalse($result->hasSuccesses());
        self::assertFalse($result->hasFailures());
    }

    private function makeStore(int $id, bool $withUser = true): Store
    {
        $store = $this->createStub(Store::class);
        $store->method('getId')->willReturn($id);
        $store->method('getUser')->willReturn($withUser ? new User() : null);

        return $store;
    }
}
