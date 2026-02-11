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

        self::assertInstanceOf(User::class, $unserialized);
        self::assertSame('test@example.com', $unserialized->getEmail());
    }

    public function testSerializeContainsIdAndEmail(): void
    {
        $user = new User();
        $user->setEmail('serialize@example.com');
        $user->setName('Serialize Test');
        $user->setGender(Gender::female);

        $data = $user->__serialize();

        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('email', $data);
        self::assertSame('serialize@example.com', $data['email']);
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

        self::assertSame('restored@example.com', $user->getEmail());
    }

    public function testUnserializeWithMissingData(): void
    {
        $user = new User();
        $user->setEmail('initial@example.com');
        $user->setGender(Gender::male);

        /** @var array{id: int|null, email: string|null} $incompleteData */
        $incompleteData = [];
        $user->__unserialize($incompleteData);

        self::assertNull($user->getId());
        self::assertSame('', $user->getEmail());
    }

    public function testToStringReturnsName(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('John Doe');
        $user->setGender(Gender::male);

        self::assertSame('John Doe', (string) $user);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        self::assertSame('identifier@example.com', $user->getUserIdentifier());
    }

    public function testSetIdentifierSetsEmail(): void
    {
        $user = new User();
        $user->setEmail('original@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $result = $user->setIdentifier('new@example.com');

        self::assertSame($user, $result);
        self::assertSame('new@example.com', $user->getEmail());
    }

    public function testGetRolesReturnsArrayWithSingleRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);
        $user->setRole('ROLE_ADMIN');

        $roles = $user->getRoles();

        self::assertCount(1, $roles);
        self::assertSame('ROLE_ADMIN', $roles[0]);
    }

    public function testSetRolesSetsFirstRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        $result = $user->setRoles(['ROLE_CASHIER', 'ROLE_USER']);

        self::assertSame($user, $result);
        self::assertSame('ROLE_CASHIER', $user->getRole());
    }

    public function testDefaultRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test');
        $user->setGender(Gender::male);

        self::assertSame('ROLE_USER', $user->getRole());
    }



    public function testGetPasswordReturnsNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertNull($user->getPassword());
    }

    public function testGetSaltReturnsNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertNull($user->getSalt());
    }

    public function testRolesConstant(): void
    {
        $expected = [
            'user' => 'ROLE_USER',
            'cashier' => 'ROLE_CASHIER',
            'admin' => 'ROLE_ADMIN',
        ];

        self::assertSame($expected, User::ROLES);
    }

    public function testGoogleId(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertSame('', $user->getGoogleId());

        $result = $user->setGoogleId('google-123');

        self::assertSame($user, $result);
        self::assertSame('google-123', $user->getGoogleId());
    }

    public function testIsActive(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertNull($user->isIsActive());

        $result = $user->setIsActive(true);

        self::assertSame($user, $result);
        self::assertTrue($user->isIsActive());

        $user->setIsActive(false);
        self::assertFalse($user->isIsActive());
    }

    public function testPhoneNumbers(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setTelefono('123-456-7890');
        self::assertSame($user, $result);
        self::assertSame('123-456-7890', $user->getTelefono());

        $result = $user->setTelefono2('098-765-4321');
        self::assertSame($user, $result);
        self::assertSame('098-765-4321', $user->getTelefono2());
    }

    public function testDireccion(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setDireccion('123 Main Street');

        self::assertSame($user, $result);
        self::assertSame('123 Main Street', $user->getDireccion());
    }

    public function testInqCiAndRuc(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertSame('', $user->getInqCi());
        self::assertSame('', $user->getInqRuc());

        $result = $user->setInqCi('123456789-0');
        self::assertSame($user, $result);
        self::assertSame('123456789-0', $user->getInqCi());

        $result = $user->setInqRuc('1234567890001');
        self::assertSame($user, $result);
        self::assertSame('1234567890001', $user->getInqRuc());
    }

    public function testStoresCollection(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $stores = $user->getStores();

        self::assertCount(0, $stores);
    }

    public function testAddStore(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $store = new Store();
        $store->setDestination('Test Store');

        $result = $user->addStore($store);

        self::assertSame($user, $result);
        self::assertCount(1, $user->getStores());
        self::assertTrue($user->getStores()->contains($store));
    }

    public function testRemoveStore(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $store = new Store();
        $store->setDestination('Test Store');

        $user->addStore($store);
        self::assertCount(1, $user->getStores());

        $result = $user->removeStore($store);

        self::assertSame($user, $result);
        self::assertCount(0, $user->getStores());
        self::assertFalse($user->getStores()->contains($store));
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

        self::assertCount(2, $user->getStores());
    }

    #[IgnoreDeprecations]
    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        // Should not throw - deprecated no-op method
        $user->eraseCredentials();

        self::assertSame('test@example.com', $user->getEmail());
    }

    public function testToStringWithNullName(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        self::assertSame('', (string) $user);
    }

    public function testSetInqRucToNull(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setInqRuc('1234567890001');
        self::assertSame('1234567890001', $user->getInqRuc());

        $result = $user->setInqRuc(null);
        self::assertSame($user, $result);
        self::assertNull($user->getInqRuc());
    }

    public function testNullablePhoneNumbers(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setTelefono('123');
        $user->setTelefono(null);
        self::assertNull($user->getTelefono());

        $user->setTelefono2('456');
        $user->setTelefono2(null);
        self::assertNull($user->getTelefono2());
    }

    public function testNullableDireccion(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $user->setDireccion('Some address');
        $user->setDireccion(null);
        self::assertNull($user->getDireccion());
    }

    public function testSetRoleAndGetRole(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setGender(Gender::male);

        $result = $user->setRole('ROLE_CASHIER');

        self::assertSame($user, $result);
        self::assertSame('ROLE_CASHIER', $user->getRole());
        self::assertSame(['ROLE_CASHIER'], $user->getRoles());
    }

    public function testGenderGetterSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        foreach (Gender::cases() as $gender) {
            $result = $user->setGender($gender);
            self::assertSame($user, $result);
            self::assertSame($gender, $user->getGender());
        }
    }
}
