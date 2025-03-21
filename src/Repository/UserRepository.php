<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<UserRepository>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /**
         * @var class-string<UserRepository>
         */
        $className = User::class;
        parent::__construct($registry, $className);
    }

    /**
     * Find all active users.
     *
     * @return User[]
     */
    public function findActiveUsers(): array
    {
        return $this->findBy(
            [
                'role' => 'ROLE_USER',
                'isActive' => 1,
            ],
            [
                'name' => 'ASC',
            ]
        );
    }

    /**
     * @return User[]
     */
    public function getSortedByStore(): array
    {
        $users = $this->findActiveUsers();

        usort(
            $users,
            static function (User $a, User $b): int {
                $aId = 0;
                $bId = 0;

                foreach ($a->getStores() as $store) {
                    $aId = $store->getId();
                }

                foreach ($b->getStores() as $store) {
                    $bId = $store->getId();
                }

                return ($aId < $bId) ? -1 : 1;
            }
        );

        return $users;
    }
}
