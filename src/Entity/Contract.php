<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContractRepository")
 */
class Contract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $storeNumber = 0;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private string $inqNombreapellido = '';

    /**
     * @var ?UserGender
     *
     * @ManyToOne(targetEntity="UserGender")
     */
    private ?UserGender $gender = null;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private string $inqCi = '000000000-0';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $destination = '';

    /**
     * @ORM\Column(type="float", precision=10, scale=0)
     */
    private float $valAlq = 0;

    /**
     * @ORM\Column(type="float", precision=10, scale=0)
     */
    private float $valGarantia = 0;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date")
     */
    private DateTime $date;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntLanfort = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntNeon = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntSwitch = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntToma = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntVentana = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntLlaves = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntMedAgua = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cntMedElec = 0;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $medElectrico = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $medAgua = '';

    /**
     * @ORM\Column(type="text", length=65535, nullable=false)
     */
    private string $text;

    public function __construct()
    {
        $this->date = new DateTime;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setInqNombreapellido(string $inqNombreapellido): Contract
    {
        $this->inqNombreapellido = $inqNombreapellido;

        return $this;
    }

    public function getInqNombreapellido(): string
    {
        return $this->inqNombreapellido;
    }

    public function setInqCi(string $inqCi): Contract
    {
        $this->inqCi = $inqCi;

        return $this;
    }

    public function getInqCi(): string
    {
        return $this->inqCi;
    }

    public function setDestination(string $destination): Contract
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setValAlq($valAlq): Contract
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    public function getValAlq()
    {
        return $this->valAlq;
    }

    public function setValGarantia($valGarantia): Contract
    {
        $this->valGarantia = $valGarantia;

        return $this;
    }

    public function getValGarantia()
    {
        return $this->valGarantia;
    }

    public function setDate($date): Contract
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setCntLanfort(int $cntLanfort): Contract
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    public function getCntLanfort(): int
    {
        return $this->cntLanfort;
    }

    public function setCntNeon(int $cntNeon): Contract
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    public function getCntNeon(): int
    {
        return $this->cntNeon;
    }

    public function setCntSwitch(int $cntSwitch): Contract
    {
        $this->cntSwitch = $cntSwitch;

        return $this;
    }

    public function getCntSwitch(): int
    {
        return $this->cntSwitch;
    }

    public function setCntToma(int $cntToma): Contract
    {
        $this->cntToma = $cntToma;

        return $this;
    }

    public function getCntToma(): int
    {
        return $this->cntToma;
    }

    public function setCntVentana(int $cntVentana): Contract
    {
        $this->cntVentana = $cntVentana;

        return $this;
    }

    public function getCntVentana(): int
    {
        return $this->cntVentana;
    }

    public function setCntLlaves(int $cntLlaves): Contract
    {
        $this->cntLlaves = $cntLlaves;

        return $this;
    }

    public function getCntLlaves(): int
    {
        return $this->cntLlaves;
    }

    public function setCntMedAgua(int $cntMedAgua): Contract
    {
        $this->cntMedAgua = $cntMedAgua;

        return $this;
    }

    public function getCntMedAgua(): int
    {
        return $this->cntMedAgua;
    }

    public function setCntMedElec(int $cntMedElec): Contract
    {
        $this->cntMedElec = $cntMedElec;

        return $this;
    }

    public function getCntMedElec(): int
    {
        return $this->cntMedElec;
    }

    public function setMedElectrico(string $medElectrico): Contract
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    public function getMedElectrico(): string
    {
        return $this->medElectrico;
    }

    public function setMedAgua(string $medAgua): Contract
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    public function getMedAgua(): string
    {
        return $this->medAgua;
    }

    public function setText(string $text): Contract
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setGender(UserGender $gender): Contract
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): ?UserGender
    {
        return $this->gender;
    }

    public function setStoreNumber(int $storeNumber): Contract
    {
        $this->storeNumber = $storeNumber;

        return $this;
    }

    public function getStoreNumber(): int
    {
        return $this->storeNumber;
    }

    public function setValuesFromStore(Store $store): self
    {
        $this->setStoreNumber($store->getId())
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
