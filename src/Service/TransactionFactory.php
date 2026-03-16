<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Type\TransactionType;
use DateTime;

class TransactionFactory
{
    public function createPayment(
        Store $store,
        User $user,
        PaymentMethod $method,
        string $date,
        int $recipeNo,
        int $document,
        int $depId,
        string $amount,
        string $comment,
    ): Transaction {
        return (new Transaction())
            ->setDate(new DateTime($date))
            ->setStore($store)
            ->setUser($user)
            ->setType(TransactionType::payment)
            ->setMethod($method)
            ->setRecipeNo($recipeNo)
            ->setDocument($document)
            ->setDepId($depId)
            ->setAmount($amount)
            ->setComment($comment);
    }

    public function createRent(
        Store $store,
        User $user,
        PaymentMethod $method,
        string $date,
        string $amount,
    ): Transaction {
        return (new Transaction())
            ->setDate(new DateTime($date))
            ->setStore($store)
            ->setUser($user)
            ->setType(TransactionType::rent)
            ->setMethod($method)
            ->setAmount((string) (-(float) $amount));
    }
}
