<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40.
 */
namespace App\Entity;

use App\Repository\ContractRepository;
use App\Type\Gender;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[Column, Id, GeneratedValue]
    private ?int $id = null;

    #[Column]
    protected ?int $storeNumber = null;

    #[Column(length: 150)]
    private ?string $inqNombreapellido = null;

    #[Column(enumType: Gender::class)]
    private Gender $gender;

    #[Column(length: 11)]
    private string $inqCi = '000000000-0';

    #[Column(length: 50)]
    private ?string $destination = null;

    #[Column]
    private ?float $valAlq = null;

    #[Column]
    private ?float $valGarantia = null;

    #[Column(type: Types::DATE_MUTABLE)]
    private \DateTime $date;

    #[Column]
    private ?int $cntLanfort = 0;

    #[Column]
    private ?int $cntNeon = 0;

    #[Column]
    private int $cntSwitch = 0;

    #[Column]
    private int $cntToma = 0;

    #[Column]
    private int $cntVentana = 0;

    #[Column]
    private int $cntLlaves = 0;

    #[Column]
    private int $cntMedAgua = 0;

    #[Column]
    private int $cntMedElec = 0;

    #[Column(length: 50)]
    private ?string $medElectrico = '';

    #[Column(length: 50)]
    private ?string $medAgua = '';

    #[Column(type: Types::TEXT, length: 65535, nullable: false)]
    private string $text = '';

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setInqNombreapellido(string $inqNombreapellido): static
    {
        $this->inqNombreapellido = $inqNombreapellido;

        return $this;
    }

    public function getInqNombreapellido(): ?string
    {
        return $this->inqNombreapellido;
    }

    public function setInqCi(string $inqCi): static
    {
        $this->inqCi = $inqCi;

        return $this;
    }

    public function getInqCi(): string
    {
        return $this->inqCi;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setValAlq(float $valAlq): static
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    public function getValAlq(): ?float
    {
        return $this->valAlq;
    }

    public function setValGarantia(float $valGarantia): static
    {
        $this->valGarantia = $valGarantia;

        return $this;
    }

    public function getValGarantia(): ?float
    {
        return $this->valGarantia;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setCntLanfort(int $cntLanfort): static
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    public function getCntLanfort(): ?int
    {
        return $this->cntLanfort;
    }

    public function setCntNeon(int $cntNeon): static
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    public function getCntNeon(): ?int
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

    public function setMedElectrico(string $medElectrico): static
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    public function getMedElectrico(): ?string
    {
        return $this->medElectrico;
    }

    public function setMedAgua(string $medAgua): static
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    public function getMedAgua(): ?string
    {
        return $this->medAgua;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setStoreNumber(int $storeNumber): static
    {
        $this->storeNumber = $storeNumber;

        return $this;
    }

    public function getStoreNumber(): ?int
    {
        return $this->storeNumber;
    }

    public function setValuesFromStore(Store $store): self
    {
        $this->setStoreNumber((int)$store->getId())
            ->setDestination($store->getDestination())
            ->setValAlq($store->getValAlq())
            ->setCntLanfort($store->getCntLanfort())
            ->setCntLlaves($store->getCntLlaves())
            ->setCntMedAgua($store->getCntMedAgua())
            ->setCntMedElec($store->getCntMedElec())
            ->setCntNeon($store->getCntNeon())
            ->setCntSwitch($store->getCntSwitch())
            ->setCntToma($store->getCntToma())
            ->setCntVentana($store->getCntVentana())
            ->setMedElectrico($store->getMedElectrico())
            ->setMedAgua($store->getMedAgua());

        return $this;
    }
}
