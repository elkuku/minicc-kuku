<?php

declare(strict_types=1);

namespace App\Tests\Type;

use App\Type\GoogleUser;
use PHPUnit\Framework\TestCase;

final class GoogleUserTest extends TestCase
{
    private GoogleUser $googleUser;

    protected function setUp(): void
    {
        $this->googleUser = new GoogleUser([
            'sub' => '123456789',
            'name' => 'John Doe',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'locale' => 'en_US',
            'email' => 'john.doe@example.com',
            'hd' => 'example.com',
            'picture' => 'https://example.com/avatar.jpg',
        ]);
    }

    public function testGetId(): void
    {
        self::assertSame('123456789', $this->googleUser->getId());
    }

    public function testGetName(): void
    {
        self::assertSame('John Doe', $this->googleUser->getName());
    }

    public function testGetFirstName(): void
    {
        self::assertSame('John', $this->googleUser->getFirstName());
    }

    public function testGetLastName(): void
    {
        self::assertSame('Doe', $this->googleUser->getLastName());
    }

    public function testGetLocale(): void
    {
        self::assertSame('en_US', $this->googleUser->getLocale());
    }

    public function testGetEmail(): void
    {
        self::assertSame('john.doe@example.com', $this->googleUser->getEmail());
    }

    public function testGetHostedDomain(): void
    {
        self::assertSame('example.com', $this->googleUser->getHostedDomain());
    }

    public function testGetAvatar(): void
    {
        self::assertSame('https://example.com/avatar.jpg', $this->googleUser->getAvatar());
    }

    public function testToArray(): void
    {
        $expected = [
            'sub' => '123456789',
            'name' => 'John Doe',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'locale' => 'en_US',
            'email' => 'john.doe@example.com',
            'hd' => 'example.com',
            'picture' => 'https://example.com/avatar.jpg',
        ];

        self::assertSame($expected, $this->googleUser->toArray());
    }

    public function testMissingOptionalFieldsReturnNull(): void
    {
        $minimalUser = new GoogleUser([
            'sub' => '999',
            'name' => 'Minimal User',
        ]);

        self::assertSame('999', $minimalUser->getId());
        self::assertSame('Minimal User', $minimalUser->getName());
        self::assertNull($minimalUser->getFirstName());
        self::assertNull($minimalUser->getLastName());
        self::assertNull($minimalUser->getLocale());
        self::assertNull($minimalUser->getEmail());
        self::assertNull($minimalUser->getHostedDomain());
        self::assertNull($minimalUser->getAvatar());
    }

    public function testPartialData(): void
    {
        $partialUser = new GoogleUser([
            'sub' => '456',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        self::assertSame('456', $partialUser->getId());
        self::assertSame('Jane Smith', $partialUser->getName());
        self::assertSame('jane@example.com', $partialUser->getEmail());
        self::assertNull($partialUser->getFirstName());
        self::assertNull($partialUser->getAvatar());
    }
}
