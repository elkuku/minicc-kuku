<?php

namespace App\Security;

use App\Entity\Store;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class StoreVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const EXPORT = 'export';

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::EXPORT])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof Store) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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

        switch ($attribute) {
            case self::VIEW:
            case self::EXPORT:
                return $this->canView($store, $user);
            case self::EDIT:
                return $this->canEdit($store, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Store $store, User $user)
    {
        return $store->getUser() === $user;
    }

    private function canEdit(Store $store, User $user)
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
