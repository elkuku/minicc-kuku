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
        $this->assertSame('123456789', $this->googleUser->getId());
    }

    public function testGetName(): void
    {
        $this->assertSame('John Doe', $this->googleUser->getName());
    }

    public function testGetFirstName(): void
    {
        $this->assertSame('John', $this->googleUser->getFirstName());
    }

    public function testGetLastName(): void
    {
        $this->assertSame('Doe', $this->googleUser->getLastName());
    }

    public function testGetLocale(): void
    {
        $this->assertSame('en_US', $this->googleUser->getLocale());
    }

    public function testGetEmail(): void
    {
        $this->assertSame('john.doe@example.com', $this->googleUser->getEmail());
    }

    public function testGetHostedDomain(): void
    {
        $this->assertSame('example.com', $this->googleUser->getHostedDomain());
    }

    public function testGetAvatar(): void
    {
        $this->assertSame('https://example.com/avatar.jpg', $this->googleUser->getAvatar());
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

        $this->assertSame($expected, $this->googleUser->toArray());
    }

    public function testMissingOptionalFieldsReturnNull(): void
    {
        $minimalUser = new GoogleUser([
            'sub' => '999',
            'name' => 'Minimal User',
        ]);

        $this->assertSame('999', $minimalUser->getId());
        $this->assertSame('Minimal User', $minimalUser->getName());
        $this->assertNull($minimalUser->getFirstName());
        $this->assertNull($minimalUser->getLastName());
        $this->assertNull($minimalUser->getLocale());
        $this->assertNull($minimalUser->getEmail());
        $this->assertNull($minimalUser->getHostedDomain());
        $this->assertNull($minimalUser->getAvatar());
    }

    public function testPartialData(): void
    {
        $partialUser = new GoogleUser([
            'sub' => '456',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $this->assertSame('456', $partialUser->getId());
        $this->assertSame('Jane Smith', $partialUser->getName());
        $this->assertSame('jane@example.com', $partialUser->getEmail());
        $this->assertNull($partialUser->getFirstName());
        $this->assertNull($partialUser->getAvatar());
    }
}
