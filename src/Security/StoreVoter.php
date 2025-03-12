<?php

declare(strict_types=1);

namespace App\Security;

use LogicException;
use App\Entity\Store;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, string>
 */
class StoreVoter extends Voter
{
    final public const string VIEW = 'view';

    final public const string EDIT = 'edit';

    final public const string EXPORT = 'export';

    public function __construct(
        private readonly Security $security
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::EXPORT])) {
            return false;
        }

        if (!$subject instanceof Store) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(
        string         $attribute,
        mixed          $subject,
        TokenInterface $token
    ): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Store $store */
        $store = $subject;

        return match ($attribute) {
            self::VIEW, self::EXPORT => $this->canView($store, $user),
            self::EDIT => $this->canEdit(),
            default => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canView(Store $store, User $user): bool
    {
        if ($this->security->isGranted(User::ROLES['cashier'])) {
            return true;
        }

        return $store->getUser() === $user;
    }

    private function canEdit(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
