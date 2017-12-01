<?php

namespace App\Repository;

/**
 * UserRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class UserRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findActiveUsers()
    {
        return $this->findBy(
            ['role' => 'ROLE_USER', 'state' => 1],
            ['name' => 'ASC']
        );
    }
}
