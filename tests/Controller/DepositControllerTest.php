<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Deposit;
use App\Entity\User;
use App\Repository\DepositRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class DepositControllerTest extends WebTestCase
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

    public function testDepositIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/deposits');

        self::assertResponseIsSuccessful();
    }

    public function testDepositSearchReturnsResults(): void
    {
        $this->client->request(Request::METHOD_GET, '/deposits/search?q=123');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('deposits_search');
    }

    public function testDepositLookupReturnsJson(): void
    {
        $deposit = $this->getDeposit();
        $depositId = $deposit->getId();

        $this->client->request(Request::METHOD_GET, '/deposits/lookup?id=' . $depositId);

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertSame('application/json', $response->headers->get('Content-Type'));

        $data = json_decode((string) $response->getContent(), true);
        self::assertIsArray($data);
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('amount', $data);
        self::assertArrayHasKey('document', $data);
        self::assertArrayHasKey('date', $data);
        self::assertArrayHasKey('entity', $data);
    }

    public function testDepositLookupReturnsNullForMissing(): void
    {
        $this->client->request(Request::METHOD_GET, '/deposits/lookup?id=999999');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertSame('application/json', $response->headers->get('Content-Type'));
        self::assertSame('null', $response->getContent());
    }

    public function testDepositDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/deposits');

        self::assertResponseStatusCodeSame(403);
    }

    public function testDepositUploadWithoutFile(): void
    {
        $this->client->request(Request::METHOD_POST, '/deposits/upload');

        self::assertResponseStatusCodeSame(500);
    }

    public function testDepositDelete(): void
    {
        $deposit = $this->getDeposit();
        $depositId = $deposit->getId();

        $this->client->request(Request::METHOD_GET, '/deposits/delete/' . $depositId);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('deposits_index');
    }

    private function getDeposit(): Deposit
    {
        /** @var DepositRepository $depositRepository */
        $depositRepository = self::getContainer()->get(DepositRepository::class);
        $deposit = $depositRepository->findOneBy([]);
        $this->assertInstanceOf(Deposit::class, $deposit);

        return $deposit;
    }
}
