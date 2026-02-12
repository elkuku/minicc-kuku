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

        $this->assertNull($store->getId());
    }

    public function testSetId(): void
    {
        $store = new Store();

        $result = $store->setId(42);

        $this->assertSame($store, $result);
        $this->assertSame(42, $store->getId());
    }

    public function testToString(): void
    {
        $store = new Store();
        $this->setStoreId($store, 5);
        $store->setDestination('Centro Comercial');

        $result = (string) $store;

        $this->assertSame('5 - Centro Comercial', $result);
    }

    public function testToStringWithNullId(): void
    {
        $store = new Store();
        $store->setDestination('Test Store');

        $result = (string) $store;

        // Store id is null but sprintf %d converts null to 0
        $this->assertSame('0 - Test Store', $result);
    }

    public function testUserIdGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getUserId());

        $result = $store->setUserId(123);

        $this->assertSame($store, $result);
        $this->assertSame(123, $store->getUserId());
    }

    public function testDestinationGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame('', $store->getDestination());

        $result = $store->setDestination('Local 101');

        $this->assertSame($store, $result);
        $this->assertSame('Local 101', $store->getDestination());
    }

    public function testValAlqGetterSetter(): void
    {
        $store = new Store();

        $this->assertEqualsWithDelta(0.0, $store->getValAlq(), PHP_FLOAT_EPSILON);

        $result = $store->setValAlq(500.50);

        $this->assertSame($store, $result);
        $this->assertEqualsWithDelta(500.50, $store->getValAlq(), PHP_FLOAT_EPSILON);
    }

    public function testCntLanfortGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntLanfort());

        $result = $store->setCntLanfort(5);

        $this->assertSame($store, $result);
        $this->assertSame(5, $store->getCntLanfort());
    }

    public function testCntNeonGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntNeon());

        $result = $store->setCntNeon(3);

        $this->assertSame($store, $result);
        $this->assertSame(3, $store->getCntNeon());
    }

    public function testCntSwitchGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntSwitch());

        $result = $store->setCntSwitch(8);

        $this->assertSame($store, $result);
        $this->assertSame(8, $store->getCntSwitch());
    }

    public function testCntTomaGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntToma());

        $result = $store->setCntToma(10);

        $this->assertSame($store, $result);
        $this->assertSame(10, $store->getCntToma());
    }

    public function testCntVentanaGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntVentana());

        $result = $store->setCntVentana(4);

        $this->assertSame($store, $result);
        $this->assertSame(4, $store->getCntVentana());
    }

    public function testCntLlavesGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntLlaves());

        $result = $store->setCntLlaves(2);

        $this->assertSame($store, $result);
        $this->assertSame(2, $store->getCntLlaves());
    }

    public function testCntMedAguaGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntMedAgua());

        $result = $store->setCntMedAgua(1);

        $this->assertSame($store, $result);
        $this->assertSame(1, $store->getCntMedAgua());
    }

    public function testCntMedElecGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame(0, $store->getCntMedElec());

        $result = $store->setCntMedElec(1);

        $this->assertSame($store, $result);
        $this->assertSame(1, $store->getCntMedElec());
    }

    public function testMedAguaGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame('', $store->getMedAgua());

        $result = $store->setMedAgua('AGUA-001');

        $this->assertSame($store, $result);
        $this->assertSame('AGUA-001', $store->getMedAgua());
    }

    public function testMedElectricoGetterSetter(): void
    {
        $store = new Store();

        $this->assertSame('', $store->getMedElectrico());

        $result = $store->setMedElectrico('ELEC-001');

        $this->assertSame($store, $result);
        $this->assertSame('ELEC-001', $store->getMedElectrico());
    }

    public function testUserGetterSetter(): void
    {
        $store = new Store();
        $user = $this->createUser();

        $this->assertNotInstanceOf(User::class, $store->getUser());

        $result = $store->setUser($user);

        $this->assertSame($store, $result);
        $this->assertSame($user, $store->getUser());
    }

    public function testSetUserToNull(): void
    {
        $store = new Store();
        $user = $this->createUser();

        $store->setUser($user);
        $store->setUser(null);

        $this->assertNotInstanceOf(User::class, $store->getUser());
    }

    public function testAllPropertiesDefaultValues(): void
    {
        $store = new Store();

        $this->assertNull($store->getId());
        $this->assertSame(0, $store->getUserId());
        $this->assertSame('', $store->getDestination());
        $this->assertEqualsWithDelta(0.0, $store->getValAlq(), PHP_FLOAT_EPSILON);
        $this->assertSame(0, $store->getCntLanfort());
        $this->assertSame(0, $store->getCntNeon());
        $this->assertSame(0, $store->getCntSwitch());
        $this->assertSame(0, $store->getCntToma());
        $this->assertSame(0, $store->getCntVentana());
        $this->assertSame(0, $store->getCntLlaves());
        $this->assertSame(0, $store->getCntMedAgua());
        $this->assertSame(0, $store->getCntMedElec());
        $this->assertSame('', $store->getMedAgua());
        $this->assertSame('', $store->getMedElectrico());
        $this->assertNotInstanceOf(User::class, $store->getUser());
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
