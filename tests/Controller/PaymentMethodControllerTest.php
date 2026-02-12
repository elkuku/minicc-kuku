<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentMethodControllerTest extends WebTestCase
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

    public function testPaymentMethodIndex(): void
    {
        $this->client->request('GET', '/payment-methods');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('payment_methods_index');
    }

    public function testPaymentMethodIndexAjax(): void
    {
        $this->client->request('GET', '/payment-methods?ajax=1');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('payment_methods_index');
    }

    public function testPaymentMethodCreateGetForm(): void
    {
        $this->client->request('GET', '/payment-methods/create');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('payment_methods_create');
    }

    public function testPaymentMethodEditGetForm(): void
    {
        /** @var PaymentMethodRepository $paymentMethodRepository */
        $paymentMethodRepository = static::getContainer()->get(PaymentMethodRepository::class);
        $method = $paymentMethodRepository->findOneBy(['name' => 'Bar']);
        $this->assertInstanceOf(PaymentMethod::class, $method);
        $methodId = $method->getId();

        $this->client->request('GET', '/payment-methods/edit/' . $methodId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('payment_methods_edit');
    }

    public function testPaymentMethodCreatePostValidForm(): void
    {
        $this->client->request('GET', '/payment-methods/create');
        $this->client->submitForm('Guardar', [
            'payment_method[name]' => 'test-new-method',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('payment_methods_index');
    }

    public function testPaymentMethodEditPostValidForm(): void
    {
        /** @var PaymentMethodRepository $paymentMethodRepository */
        $paymentMethodRepository = static::getContainer()->get(PaymentMethodRepository::class);
        $method = $paymentMethodRepository->findOneBy(['name' => 'gye-1005345']);
        $this->assertInstanceOf(PaymentMethod::class, $method);
        $methodId = $method->getId();

        $this->client->request('GET', '/payment-methods/edit/' . $methodId);
        $this->client->submitForm('Guardar', [
            'payment_method[name]' => 'gye-1005345-updated',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('payment_methods_index');
    }

    public function testPaymentMethodDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request('GET', '/payment-methods');

        self::assertResponseStatusCodeSame(403);
    }

    // Delete test last since it modifies the database
    public function testPaymentMethodDelete(): void
    {
        $method = $this->ensurePaymentMethodForDelete();
        $methodId = $method->getId();

        $this->client->request('GET', '/payment-methods/delete/' . $methodId);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('payment_methods_index');
    }

    private function ensurePaymentMethodForDelete(): PaymentMethod
    {
        /** @var PaymentMethodRepository $paymentMethodRepository */
        $paymentMethodRepository = static::getContainer()->get(PaymentMethodRepository::class);
        $method = $paymentMethodRepository->findOneBy(['name' => 'pch-765']);

        if ($method) {
            return $method;
        }

        $method = new PaymentMethod();
        $method->setName('test-delete-me');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($method);
        $em->flush();

        return $method;
    }
}
