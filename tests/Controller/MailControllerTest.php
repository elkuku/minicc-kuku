<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MailControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertNotNull($admin);
        $this->client->loginUser($admin);
    }

    public function testTransactionsClientsGetForm(): void
    {
        $this->client->request('GET', '/mail/transactions-clients');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_transactions_clients');
    }

    public function testPlanillasClientsGetForm(): void
    {
        $this->client->request('GET', '/mail/planillas-clients');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_planillas_clients');
    }

    public function testPaymentsAccountantGetForm(): void
    {
        $this->client->request('GET', '/mail/payments-accountant');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_payments_accountant');
    }

    public function testPaymentsAccountantPostSendsEmail(): void
    {
        $this->client->request('POST', '/mail/payments-accountant', [
            'year' => 2024,
            'month' => 1,
            'ids' => ['1'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_payments_accountant');
    }

    public function testTransactionsClientsDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/mail/transactions-clients');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPlanillasClientsDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/mail/planillas-clients');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPaymentsAccountantDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/mail/payments-accountant');

        self::assertResponseStatusCodeSame(403);
    }
}
