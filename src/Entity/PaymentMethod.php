<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Entity(repositoryClass: PaymentMethodRepository::class)]
class PaymentMethod
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column(length: 150, nullable: false), NotBlank]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
