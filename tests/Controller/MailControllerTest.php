<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MailControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $admin);
        $this->client->loginUser($admin);
    }

    public function testTransactionsClientsGetForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/mail/transactions-clients');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_transactions_clients');
    }

    public function testPlanillasClientsGetForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/mail/planillas-clients');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_planillas_clients');
    }

    public function testPaymentsAccountantGetForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/mail/payments-accountant');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_payments_accountant');
    }

    public function testPaymentsAccountantPostSendsEmail(): void
    {
        $this->client->request(Request::METHOD_POST, '/mail/payments-accountant', [
            'year' => 2024,
            'month' => 1,
            'ids' => ['1'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_payments_accountant');
    }

    public function testPaymentsAccountantRejectsInvalidMonth(): void
    {
        $this->client->request(Request::METHOD_POST, '/mail/payments-accountant', [
            'year' => 2024,
            'month' => 13,
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
    }

    public function testTransactionsClientsDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/mail/transactions-clients');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPlanillasClientsDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/mail/planillas-clients');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPaymentsAccountantDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/mail/payments-accountant');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPlanillasSendsEmail(): void
    {
        $pdfMock = $this->createStub(Pdf::class);
        $pdfMock->method('getOutputFromHtml')
            ->willReturn('%PDF-fake-content');

        self::getContainer()->set('knp_snappy.pdf', $pdfMock);

        $this->client->request(Request::METHOD_GET, '/mail/planillas');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('mail_planillas');
        $this->assertSelectorExists('.alert-success');
    }

    public function testPlanillasDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/mail/planillas');

        self::assertResponseStatusCodeSame(403);
    }

    public function testTransactionsClientsSendsToSelectedStores(): void
    {
        $this->stubPdf();
        $store = $this->getStoreWithUser();

        $this->client->request(Request::METHOD_POST, '/mail/transactions-clients', [
            'recipients' => [(int) $store->getId() => 'on'],
            'year' => '2024',
        ]);

        self::assertResponseRedirects('/');
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertNotNull($email);
        self::assertEmailHeaderSame($email, 'Subject', sprintf('Movimientos del local %d ano 2024', $store->getId()));
    }

    public function testTransactionsClientsRejectsInvalidYear(): void
    {
        $store = $this->getStoreWithUser();

        $this->client->request(Request::METHOD_POST, '/mail/transactions-clients', [
            'recipients' => [(int) $store->getId() => 'on'],
            'year' => 'abc',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
    }

    public function testPlanillasClientsSendsToSelectedStores(): void
    {
        $this->stubPdf();
        $store = $this->getStoreWithUser();

        $this->client->request(Request::METHOD_POST, '/mail/planillas-clients', [
            'recipients' => [(int) $store->getId() => 'on'],
        ]);

        self::assertResponseRedirects('/');
        self::assertEmailCount(1);
    }

    private function stubPdf(): void
    {
        $pdfMock = $this->createStub(Pdf::class);
        $pdfMock->method('getOutputFromHtml')
            ->willReturn('%PDF-fake-content');

        self::getContainer()->set('knp_snappy.pdf', $pdfMock);
    }

    private function getStoreWithUser(): Store
    {
        /** @var StoreRepository $storeRepository */
        $storeRepository = self::getContainer()->get(StoreRepository::class);

        foreach ($storeRepository->getActive() as $store) {
            if ($store->getUser() !== null) {
                return $store;
            }
        }

        self::fail('No active store with a user found in fixtures.');
    }
}
