<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Repository\UserRepository;
use App\Type\Gender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContractControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $admin);
        $this->client->loginUser($admin);
    }

    public function testContractIndex(): void
    {
        $this->client->request(Request::METHOD_GET, '/contracts');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('contracts_index');
    }

    public function testContractIndexWithFilters(): void
    {
        $this->client->request(Request::METHOD_POST, '/contracts', [
            'store_id' => 1,
            'year' => 2024,
        ]);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('contracts_index');
    }

    public function testContractEditGetForm(): void
    {
        $contract = $this->ensureContractExists();
        $contractId = $contract->getId();

        $this->client->request(Request::METHOD_GET, '/contracts/edit/' . $contractId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('contracts_edit');
    }

    public function testContractEditPostForm(): void
    {
        $contract = $this->ensureContractExists();
        $contractId = $contract->getId();

        $crawler = $this->client->request(Request::METHOD_GET, '/contracts/edit/' . $contractId);
        $form = $crawler->selectButton('Guardar')->form();
        $form['contract[inqNombreApellido]'] = 'Updated Tester';
        $form['contract[destination]'] = 'Updated Destination';
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('contracts_index');
    }

    public function testContractTemplateStringsReturnsJson(): void
    {
        $this->client->request(Request::METHOD_GET, '/contracts/template-strings');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testContractDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/contracts');

        self::assertResponseStatusCodeSame(403);
    }

    // Delete test last since it modifies the database
    public function testContractDelete(): void
    {
        $contract = $this->ensureContractExists();
        $contractId = $contract->getId();

        $this->client->request(Request::METHOD_GET, '/contracts/delete/' . $contractId);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('contracts_index');
    }

    private function ensureContractExists(): Contract
    {
        /** @var ContractRepository $contractRepository */
        $contractRepository = static::getContainer()->get(ContractRepository::class);
        $contract = $contractRepository->findOneBy([]);

        if ($contract) {
            return $contract;
        }

        $contract = new Contract();
        $contract->setGender(Gender::other);
        $contract->setStoreNumber(1);
        $contract->setInqNombreapellido('Test Contract');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($contract);
        $em->flush();

        return $contract;
    }
}
