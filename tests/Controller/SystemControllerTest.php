<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SystemControllerTest extends WebTestCase
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

    public function testAboutPage(): void
    {
        $this->client->request(Request::METHOD_GET, '/system/about');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('system_about');
    }

    public function testAboutPageDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/system/about');

        self::assertResponseStatusCodeSame(403);
    }

    public function testLogviewPage(): void
    {
        $this->client->request(Request::METHOD_GET, '/system/logview');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('system_logview');
    }

    public function testLogviewPageWithLogFile(): void
    {
        /** @var string $projectDir */
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $logDir = $projectDir . '/var/log';
        $logFile = $logDir . '/deploy.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logContent = <<<'LOG'
>>>==============
2024-01-15 10:30:00 Deploy started
Updated files
<<<===========

>>>==============
2024-02-20 14:45:00 Deploy v2
Fixed bugs
<<<===========
LOG;

        file_put_contents($logFile, $logContent);

        try {
            $this->client->request(Request::METHOD_GET, '/system/logview');

            self::assertResponseIsSuccessful();
            self::assertRouteSame('system_logview');
        } finally {
            unlink($logFile);
        }
    }

    public function testLogviewPageDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request(Request::METHOD_GET, '/system/logview');

        self::assertResponseStatusCodeSame(403);
    }
}
