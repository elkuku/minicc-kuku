<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\AppUserChecker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\HttpFoundation\Request;

final class AppUserCheckerTest extends WebTestCase
{
    public function testCheckPreAuthRejectsDeactivatedUser(): void
    {
        self::bootKernel();
        $user = new User();
        $user->setIsActive(false);

        $this->expectException(DisabledException::class);
        new AppUserChecker()->checkPreAuth($user);
    }

    public function testCheckPostAuthRejectsDeactivatedUser(): void
    {
        self::bootKernel();
        $user = new User();
        $user->setIsActive(false);

        $this->expectException(DisabledException::class);
        new AppUserChecker()->checkPostAuth($user);
    }

    #[DoesNotPerformAssertions]
    public function testCheckPreAuthAllowsActiveUser(): void
    {
        self::bootKernel();
        $user = new User();
        $user->setIsActive(true);

        new AppUserChecker()->checkPreAuth($user);
    }

    public function testDeactivatedUserIsRejectedByRealLoginFlow(): void
    {
        $client = self::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertInstanceOf(User::class, $admin);

        $admin->setIsActive(false);
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($admin);
        $em->flush();

        $crawler = $client->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'identifier' => 'admin@example.com',
        ]);
        $client->submit($form);

        // The user_checker must reject the authenticate() attempt, so the
        // login redirects back to /login rather than succeeding.
        self::assertResponseRedirects('/login');
        $client->followRedirect();

        // And no session was actually established for this deactivated user.
        $client->request(Request::METHOD_GET, '/transactions');
        self::assertResponseRedirects('/login');
    }
}