<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use App\Repository\StoreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: StoreRepository::class)]
class Store
{
    #[Id, GeneratedValue(strategy: 'AUTO')]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $userId = 0;

    #[Column(type: Types::STRING, length: 50, nullable: false)]
    private string $destination = '';

    #[Column(type: Types::FLOAT, precision: 10, scale: 0, nullable: false)]
    private float $valAlq = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntLanfort = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntNeon = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntSwitch = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntToma = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntVentana = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntLlaves = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntMedAgua = 0;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $cntMedElec = 0;

    #[Column(type: Types::STRING, length: 50)]
    private string $medElectrico = '';

    #[Column(type: Types::STRING, length: 50)]
    private string $medAgua = '';

    #[ManyToOne(targetEntity: 'User', inversedBy: 'stores')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUserId(int $idUser): static
    {
        $this->userId = $idUser;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setValAlq(float $valAlq): static
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    public function getValAlq(): float
    {
        return $this->valAlq;
    }

    public function setCntLanfort(int $cntLanfort): static
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    public function getCntLanfort(): int
    {
        return $this->cntLanfort;
    }

    public function setCntNeon(int $cntNeon): static
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    public function getCntNeon(): int
    {
        return $this->cntNeon;
    }

    public function setCntSwitch(int $cntSwitch): static
    {
        $this->cntSwitch = $cntSwitch;

        return $this;
    }

    public function getCntSwitch(): int
    {
        return $this->cntSwitch;
    }

    public function setCntToma(int $cntToma): static
    {
        $this->cntToma = $cntToma;

        return $this;
    }

    public function getCntToma(): int
    {
        return $this->cntToma;
    }

    public function setCntVentana(int $cntVentana): static
    {
        $this->cntVentana = $cntVentana;

        return $this;
    }

    public function getCntVentana(): int
    {
        return $this->cntVentana;
    }

    public function setCntLlaves(int $cntLlaves): static
    {
        $this->cntLlaves = $cntLlaves;

        return $this;
    }

    public function getCntLlaves(): int
    {
        return $this->cntLlaves;
    }

    public function setCntMedAgua(int $cntMedAgua): static
    {
        $this->cntMedAgua = $cntMedAgua;

        return $this;
    }

    public function getCntMedAgua(): int
    {
        return $this->cntMedAgua;
    }

    public function setCntMedElec(int $cntMedElec): static
    {
        $this->cntMedElec = $cntMedElec;

        return $this;
    }

    public function getCntMedElec(): int
    {
        return $this->cntMedElec;
    }

    public function setMedElectrico(int $medElectrico): static
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    public function getMedElectrico(): string
    {
        return $this->medElectrico;
    }

    public function setMedAgua(int $medAgua): static
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    public function getMedAgua(): string
    {
        return $this->medAgua;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
