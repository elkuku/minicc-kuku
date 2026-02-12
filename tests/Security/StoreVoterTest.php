<?php

declare(strict_types=1);

namespace App\Tests\Security;

use ReflectionMethod;
use App\Entity\Store;
use App\Entity\User;
use App\Security\StoreVoter;
use App\Type\Gender;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class StoreVoterTest extends TestCase
{
    public function testSupportsReturnsTrueForViewAttribute(): void
    {
        $voter = new StoreVoter($this->createStub(Security::class));
        $store = new Store();

        $method = new ReflectionMethod(StoreVoter::class, 'supports');

        $this->assertTrue($method->invoke($voter, StoreVoter::VIEW, $store));
    }

    public function testSupportsReturnsTrueForEditAttribute(): void
    {
        $voter = new StoreVoter($this->createStub(Security::class));
        $store = new Store();

        $method = new ReflectionMethod(StoreVoter::class, 'supports');

        $this->assertTrue($method->invoke($voter, StoreVoter::EDIT, $store));
    }

    public function testSupportsReturnsTrueForExportAttribute(): void
    {
        $voter = new StoreVoter($this->createStub(Security::class));
        $store = new Store();

        $method = new ReflectionMethod(StoreVoter::class, 'supports');

        $this->assertTrue($method->invoke($voter, StoreVoter::EXPORT, $store));
    }

    public function testSupportsReturnsFalseForUnsupportedAttribute(): void
    {
        $voter = new StoreVoter($this->createStub(Security::class));
        $store = new Store();

        $method = new ReflectionMethod(StoreVoter::class, 'supports');

        $this->assertFalse($method->invoke($voter, 'delete', $store));
    }

    public function testSupportsReturnsFalseForNonStoreSubject(): void
    {
        $voter = new StoreVoter($this->createStub(Security::class));

        $method = new ReflectionMethod(StoreVoter::class, 'supports');

        $this->assertFalse($method->invoke($voter, StoreVoter::VIEW, 'not-a-store'));
    }

    public function testVoteOnAttributeGrantsAccessForAdmin(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(true);

        $voter = new StoreVoter($security);
        $token = $this->createStub(TokenInterface::class);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertTrue($method->invoke($voter, StoreVoter::VIEW, new Store(), $token));
    }

    public function testVoteOnAttributeDeniesAccessWhenNotUserInstance(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(false);

        $voter = new StoreVoter($security);
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertFalse($method->invoke($voter, StoreVoter::VIEW, new Store(), $token));
    }

    public function testVoteOnAttributeViewGrantsAccessForCashier(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturnCallback(fn(string $role): bool => match ($role) {
                'ROLE_ADMIN' => false,
                User::ROLES['cashier'] => true,
                default => false,
            });

        $voter = new StoreVoter($security);
        $user = $this->createUser();
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertTrue($method->invoke($voter, StoreVoter::VIEW, new Store(), $token));
    }

    public function testVoteOnAttributeViewGrantsAccessForStoreOwner(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(false);

        $voter = new StoreVoter($security);
        $user = $this->createUser();
        $store = new Store();
        $store->setUser($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertTrue($method->invoke($voter, StoreVoter::VIEW, $store, $token));
    }

    public function testVoteOnAttributeViewDeniesAccessForNonOwner(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(false);

        $voter = new StoreVoter($security);
        $user = $this->createUser();
        $otherUser = $this->createUser('other@example.com');
        $store = new Store();
        $store->setUser($otherUser);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertFalse($method->invoke($voter, StoreVoter::VIEW, $store, $token));
    }

    public function testVoteOnAttributeExportGrantsAccessForStoreOwner(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(false);

        $voter = new StoreVoter($security);
        $user = $this->createUser();
        $store = new Store();
        $store->setUser($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertTrue($method->invoke($voter, StoreVoter::EXPORT, $store, $token));
    }

    public function testVoteOnAttributeEditDeniedForNonAdmin(): void
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')
            ->willReturn(false);

        $voter = new StoreVoter($security);
        $user = $this->createUser();

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $method = new ReflectionMethod(StoreVoter::class, 'voteOnAttribute');

        $this->assertFalse($method->invoke($voter, StoreVoter::EDIT, new Store(), $token));
    }

    public function testConstantsAreCorrect(): void
    {
        $this->assertSame('view', StoreVoter::VIEW);
        $this->assertSame('edit', StoreVoter::EDIT);
        $this->assertSame('export', StoreVoter::EXPORT);
    }

    private function createUser(string $email = 'test@example.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName('Test User');
        $user->setGender(Gender::male);

        return $user;
    }
}
