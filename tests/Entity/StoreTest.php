<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Store;
use App\Entity\User;
use App\Type\Gender;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class StoreTest extends TestCase
{
    public function testGetIdReturnsNullForNewEntity(): void
    {
        $store = new Store();

        self::assertNull($store->getId());
    }

    public function testSetId(): void
    {
        $store = new Store();

        $result = $store->setId(42);

        self::assertSame($store, $result);
        self::assertSame(42, $store->getId());
    }

    public function testToString(): void
    {
        $store = new Store();
        $this->setStoreId($store, 5);
        $store->setDestination('Centro Comercial');

        $result = (string) $store;

        self::assertSame('5 - Centro Comercial', $result);
    }

    public function testToStringWithNullId(): void
    {
        $store = new Store();
        $store->setDestination('Test Store');

        $result = (string) $store;

        // Store id is null but sprintf %d converts null to 0
        self::assertSame('0 - Test Store', $result);
    }

    public function testUserIdGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getUserId());

        $result = $store->setUserId(123);

        self::assertSame($store, $result);
        self::assertSame(123, $store->getUserId());
    }

    public function testDestinationGetterSetter(): void
    {
        $store = new Store();

        self::assertSame('', $store->getDestination());

        $result = $store->setDestination('Local 101');

        self::assertSame($store, $result);
        self::assertSame('Local 101', $store->getDestination());
    }

    public function testValAlqGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0.0, $store->getValAlq());

        $result = $store->setValAlq(500.50);

        self::assertSame($store, $result);
        self::assertSame(500.50, $store->getValAlq());
    }

    public function testCntLanfortGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntLanfort());

        $result = $store->setCntLanfort(5);

        self::assertSame($store, $result);
        self::assertSame(5, $store->getCntLanfort());
    }

    public function testCntNeonGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntNeon());

        $result = $store->setCntNeon(3);

        self::assertSame($store, $result);
        self::assertSame(3, $store->getCntNeon());
    }

    public function testCntSwitchGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntSwitch());

        $result = $store->setCntSwitch(8);

        self::assertSame($store, $result);
        self::assertSame(8, $store->getCntSwitch());
    }

    public function testCntTomaGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntToma());

        $result = $store->setCntToma(10);

        self::assertSame($store, $result);
        self::assertSame(10, $store->getCntToma());
    }

    public function testCntVentanaGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntVentana());

        $result = $store->setCntVentana(4);

        self::assertSame($store, $result);
        self::assertSame(4, $store->getCntVentana());
    }

    public function testCntLlavesGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntLlaves());

        $result = $store->setCntLlaves(2);

        self::assertSame($store, $result);
        self::assertSame(2, $store->getCntLlaves());
    }

    public function testCntMedAguaGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntMedAgua());

        $result = $store->setCntMedAgua(1);

        self::assertSame($store, $result);
        self::assertSame(1, $store->getCntMedAgua());
    }

    public function testCntMedElecGetterSetter(): void
    {
        $store = new Store();

        self::assertSame(0, $store->getCntMedElec());

        $result = $store->setCntMedElec(1);

        self::assertSame($store, $result);
        self::assertSame(1, $store->getCntMedElec());
    }

    public function testMedAguaGetterSetter(): void
    {
        $store = new Store();

        self::assertSame('', $store->getMedAgua());

        $result = $store->setMedAgua('AGUA-001');

        self::assertSame($store, $result);
        self::assertSame('AGUA-001', $store->getMedAgua());
    }

    public function testMedElectricoGetterSetter(): void
    {
        $store = new Store();

        self::assertSame('', $store->getMedElectrico());

        $result = $store->setMedElectrico('ELEC-001');

        self::assertSame($store, $result);
        self::assertSame('ELEC-001', $store->getMedElectrico());
    }

    public function testUserGetterSetter(): void
    {
        $store = new Store();
        $user = $this->createUser();

        self::assertNull($store->getUser());

        $result = $store->setUser($user);

        self::assertSame($store, $result);
        self::assertSame($user, $store->getUser());
    }

    public function testSetUserToNull(): void
    {
        $store = new Store();
        $user = $this->createUser();

        $store->setUser($user);
        $store->setUser(null);

        self::assertNull($store->getUser());
    }

    public function testAllPropertiesDefaultValues(): void
    {
        $store = new Store();

        self::assertNull($store->getId());
        self::assertSame(0, $store->getUserId());
        self::assertSame('', $store->getDestination());
        self::assertSame(0.0, $store->getValAlq());
        self::assertSame(0, $store->getCntLanfort());
        self::assertSame(0, $store->getCntNeon());
        self::assertSame(0, $store->getCntSwitch());
        self::assertSame(0, $store->getCntToma());
        self::assertSame(0, $store->getCntVentana());
        self::assertSame(0, $store->getCntLlaves());
        self::assertSame(0, $store->getCntMedAgua());
        self::assertSame(0, $store->getCntMedElec());
        self::assertSame('', $store->getMedAgua());
        self::assertSame('', $store->getMedElectrico());
        self::assertNull($store->getUser());
    }

    private function setStoreId(Store $store, int $id): void
    {
        $reflection = new ReflectionProperty(Store::class, 'id');
        $reflection->setValue($store, $id);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setGender(Gender::male);

        return $user;
    }
}
