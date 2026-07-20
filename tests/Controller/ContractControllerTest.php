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
        $this->client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
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
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/contracts');

        self::assertResponseStatusCodeSame(403);
    }

    public function testContractGeneratePdf(): void
    {
        $binary = $_ENV['WKHTMLTOPDF_PATH'] ?? $_SERVER['WKHTMLTOPDF_PATH'] ?? getenv('WKHTMLTOPDF_PATH');
        if (!is_string($binary) || !file_exists($binary)) {
            self::markTestSkipped('wkhtmltopdf not available');
        }

        $contract = $this->ensureContractExists();

        $this->client->request(Request::METHOD_GET, '/contracts/generate/' . $contract->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/pdf');

        $content = $this->client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringStartsWith('%PDF', $content);

        file_put_contents(
            dirname(__DIR__, 2) . '/var/test-contract.pdf',
            $content
        );
    }

    public function testContractGeneratePdfDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/contracts/generate/1');

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
        $contractRepository = self::getContainer()->get(ContractRepository::class);
        $contract = $contractRepository->findOneBy([]);

        if ($contract) {
            return $contract;
        }

        $contract = new Contract();
        $contract->setGender(Gender::other);
        $contract->setStoreNumber(1);
        $contract->setInqNombreapellido('Test Contract');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($contract);
        $em->flush();

        return $contract;
    }
}
