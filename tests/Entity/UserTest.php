<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Store;
use App\Entity\User;
use App\Type\Gender;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testSerializeUnserializeRoundTrip(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setGender(Gender::male);

        $serialized = serialize($user);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(User::class, $unserialized);
        $this->assertSame('test@example.com', $unserialized->getEmail());
    }

    public function testSerializeContainsIdAndEmail(): void
    {
        $user = new User();
        $user->setEmail('serialize@example.com');
        $user->setName('Serialize Test');
        $user->setGender(Gender::female);

        $data = $user->__serialize();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('serialize@example.com', $data['email']);
    }

    public function testUnserializeRestoresData(): void
    {
        $user = new User();
        $user->setEmail('initial@example.com');
        $user->setGender(Gender::male);

        $user->__unserialize([
            'id' => 42,
            'email' => 'restored@example.com',
        ]);

        $this->assertSame('restored@example.com', $user->getEmail());
    }

    public function testUnserializeWithMissingData(): void
    {
        $user = new User();
        $user->setEmail('initial@example.com');
        $user->setGender(Gender::male);

        /** @var array{id: int|null, email: string|null} $incompleteData */
        $incompleteData = [];
        $user->__unserialize($incompleteData);

        $this->assertNull($user->getId());
        $this->assertSame('', $user->getEmail());
    }

    public function testToStringReturnsName(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('John Doe');
        $user->setGender(Gender::male);

        $this->assertSame('John Doe', (string) $user);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $this->assertSame('identifier@example.com', $user->getUserIdentifier());
    }

    public function testSetIdentifierSetsEmail(): void
    {
        $user = new User();
        $user->setEmail('original@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $result = $user->setIdentifier('new@example.com');

        $this->assertSame($user, $result);
        $this->assertSame('new@example.com', $user->getEmail());
    }

    public function testGetRolesReturnsArrayWithSingleRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);
        $user->setRole('ROLE_ADMIN');

        $roles = $user->getRoles();

        $this->assertCount(1, $roles);
        $this->assertSame('ROLE_ADMIN', $roles[0]);
    }

    public function testSetRolesSetsFirstRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $result = $user->setRoles(['ROLE_CASHIER', 'ROLE_USER']);

        $this->assertSame($user, $result);
        $this->assertSame('ROLE_CASHIER', $user->getRole());
    }

    public function testDefaultRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $this->assertSame('ROLE_USER', $user->getRole());
    }



    public function testGetPasswordReturnsNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertNull($user->getPassword());
    }

    public function testGetSaltReturnsNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertNull($user->getSalt());
    }

    public function testRolesConstant(): void
    {
        $expected = [
            'user' => 'ROLE_USER',
            'cashier' => 'ROLE_CASHIER',
            'admin' => 'ROLE_ADMIN',
        ];

        $this->assertSame(User::ROLES, $expected);
    }

    public function testGoogleId(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertSame('', $user->getGoogleId());

        $result = $user->setGoogleId('google-123');

        $this->assertSame($user, $result);
        $this->assertSame('google-123', $user->getGoogleId());
    }

    public function testIsActive(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertNull($user->isIsActive());

        $result = $user->setIsActive(true);

        $this->assertSame($user, $result);
        $this->assertTrue($user->isIsActive());

        $user->setIsActive(false);
        $this->assertFalse($user->isIsActive());
    }

    public function testPhoneNumbers(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setTelefono('123-456-7890');
        $this->assertSame($user, $result);
        $this->assertSame('123-456-7890', $user->getTelefono());

        $result = $user->setTelefono2('098-765-4321');
        $this->assertSame($user, $result);
        $this->assertSame('098-765-4321', $user->getTelefono2());
    }

    public function testDireccion(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setDireccion('123 Main Street');

        $this->assertSame($user, $result);
        $this->assertSame('123 Main Street', $user->getDireccion());
    }

    public function testInqCiAndRuc(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertSame('', $user->getInqCi());
        $this->assertSame('', $user->getInqRuc());

        $result = $user->setInqCi('123456789-0');
        $this->assertSame($user, $result);
        $this->assertSame('123456789-0', $user->getInqCi());

        $result = $user->setInqRuc('1234567890001');
        $this->assertSame($user, $result);
        $this->assertSame('1234567890001', $user->getInqRuc());
    }

    public function testStoresCollection(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $stores = $user->getStores();

        $this->assertCount(0, $stores);
    }

    public function testAddStore(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $store = new Store();
        $store->setDestination('Test Store');

        $result = $user->addStore($store);

        $this->assertSame($user, $result);
        $this->assertCount(1, $user->getStores());
        $this->assertTrue($user->getStores()->contains($store));
    }

    public function testRemoveStore(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $store = new Store();
        $store->setDestination('Test Store');

        $user->addStore($store);
        $this->assertCount(1, $user->getStores());

        $result = $user->removeStore($store);

        $this->assertSame($user, $result);
        $this->assertCount(0, $user->getStores());
        $this->assertFalse($user->getStores()->contains($store));
    }

    public function testAddMultipleStores(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $store1 = new Store();
        $store1->setDestination('Store 1');

        $store2 = new Store();
        $store2->setDestination('Store 2');

        $user->addStore($store1);
        $user->addStore($store2);

        $this->assertCount(2, $user->getStores());
    }

    #[IgnoreDeprecations]
    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        // Should not throw - deprecated no-op method
        $user->eraseCredentials();

        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testToStringWithNullName(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $this->assertSame('', (string) $user);
    }

    public function testSetInqRucToNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setInqRuc('1234567890001');
        $this->assertSame('1234567890001', $user->getInqRuc());

        $result = $user->setInqRuc(null);
        $this->assertSame($user, $result);
        $this->assertNull($user->getInqRuc());
    }

    public function testNullablePhoneNumbers(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setTelefono('123');
        $user->setTelefono(null);
        $this->assertNull($user->getTelefono());

        $user->setTelefono2('456');
        $user->setTelefono2(null);
        $this->assertNull($user->getTelefono2());
    }

    public function testNullableDireccion(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setDireccion('Some address');
        $user->setDireccion(null);
        $this->assertNull($user->getDireccion());
    }

    public function testSetRoleAndGetRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setRole('ROLE_CASHIER');

        $this->assertSame($user, $result);
        $this->assertSame('ROLE_CASHIER', $user->getRole());
        $this->assertSame(['ROLE_CASHIER'], $user->getRoles());
    }

    public function testGenderGetterSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        foreach (Gender::cases() as $gender) {
            $result = $user->setGender($gender);
            $this->assertSame($user, $result);
            $this->assertSame($gender, $user->getGender());
        }
    }
}
