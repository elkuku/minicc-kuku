<?php

namespace App\Twig\Runtime;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ShaFinder;
use App\Service\TaxService;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ShaFinder $shaFinder,
        private readonly TaxService $taxService,
    )
    {
    }

    /**
     * @return User[]
     */
    public function getSystemUsers(): array
    {
        return $this->userRepository->findActiveUsers();
    }

    public function getValueWithTax(float $value): float
    {
        return $this->taxService->getValueConTax($value);
    }

    public function getTaxFromTotal(float $value): float
    {
        return $this->taxService->getTaxFromTotal($value);
    }

    public function getSHA(): string
    {
        return $this->shaFinder->getSha();
    }
}