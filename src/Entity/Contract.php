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

    /**
     * Contract constructor.
     */
    public function __construct()
    {
        $this->date = new DateTime;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set inqNombreapellido
     *
     * @param string $inqNombreapellido
     *
     * @return Contract
     */
    public function setInqNombreapellido($inqNombreapellido): Contract
    {
        $this->inqNombreapellido = $inqNombreapellido;

        return $this;
    }

    /**
     * Get inqNombreapellido
     *
     * @return string
     */
    public function getInqNombreapellido(): string
    {
        return $this->inqNombreapellido;
    }

    /**
     * Set inqCi
     *
     * @param string $inqCi
     *
     * @return Contract
     */
    public function setInqCi($inqCi): Contract
    {
        $this->inqCi = $inqCi;

        return $this;
    }

    /**
     * Get inqCi
     *
     * @return string
     */
    public function getInqCi(): string
    {
        return $this->inqCi;
    }

    /**
     * Set destination
     *
     * @param string $destination
     *
     * @return Contract
     */
    public function setDestination($destination): Contract
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Set valAlq
     *
     * @param float $valAlq
     *
     * @return Contract
     */
    public function setValAlq($valAlq): Contract
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    /**
     * Get valAlq
     *
     * @return float
     */
    public function getValAlq()
    {
        return $this->valAlq;
    }

    /**
     * Set valGarantia
     *
     * @param float $valGarantia
     *
     * @return Contract
     */
    public function setValGarantia($valGarantia): Contract
    {
        $this->valGarantia = $valGarantia;

        return $this;
    }

    /**
     * Get valGarantia
     *
     * @return float
     */
    public function getValGarantia()
    {
        return $this->valGarantia;
    }

    /**
     * Set date
     *
     * @param DateTime $date
     *
     * @return Contract
     */
    public function setDate($date): Contract
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Set cntLanfort
     *
     * @param integer $cntLanfort
     *
     * @return Contract
     */
    public function setCntLanfort($cntLanfort): Contract
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    /**
     * Get cntLanfort
     *
     * @return integer
     */
    public function getCntLanfort(): int
    {
        return $this->cntLanfort;
    }

    /**
     * Set cntNeon
     *
     * @param integer $cntNeon
     *
     * @return Contract
     */
    public function setCntNeon($cntNeon): Contract
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    /**
     * Get cntNeon
     *
     * @return integer
     */
    public function getCntNeon(): int
    {
        return $this->cntNeon;
    }

    /**
     * Set cntSwitch
     *
     * @param integer $cntSwitch
     *
     * @return Contract
     */
    public function setCntSwitch($cntSwitch): Contract
    {
        $this->cntSwitch = $cntSwitch;

        return $this;
    }

    /**
     * Get cntSwitch
     *
     * @return integer
     */
    public function getCntSwitch(): int
    {
        return $this->cntSwitch;
    }

    /**
     * Set cntToma
     *
     * @param integer $cntToma
     *
     * @return Contract
     */
    public function setCntToma($cntToma): Contract
    {
        $this->cntToma = $cntToma;

        return $this;
    }

    /**
     * Get cntToma
     *
     * @return integer
     */
    public function getCntToma(): int
    {
        return $this->cntToma;
    }

    /**
     * Set cntVentana
     *
     * @param integer $cntVentana
     *
     * @return Contract
     */
    public function setCntVentana($cntVentana): Contract
    {
        $this->cntVentana = $cntVentana;

        return $this;
    }

    /**
     * Get cntVentana
     *
     * @return integer
     */
    public function getCntVentana(): int
    {
        return $this->cntVentana;
    }

    /**
     * Set cntLlaves
     *
     * @param integer $cntLlaves
     *
     * @return Contract
     */
    public function setCntLlaves($cntLlaves): Contract
    {
        $this->cntLlaves = $cntLlaves;

        return $this;
    }

    /**
     * Get cntLlaves
     *
     * @return integer
     */
    public function getCntLlaves(): int
    {
        return $this->cntLlaves;
    }

    /**
     * Set cntMedAgua
     *
     * @param integer $cntMedAgua
     *
     * @return Contract
     */
    public function setCntMedAgua($cntMedAgua): Contract
    {
        $this->cntMedAgua = $cntMedAgua;

        return $this;
    }

    /**
     * Get cntMedAgua
     *
     * @return integer
     */
    public function getCntMedAgua(): int
    {
        return $this->cntMedAgua;
    }

    /**
     * Set cntMedElec
     *
     * @param integer $cntMedElec
     *
     * @return Contract
     */
    public function setCntMedElec($cntMedElec): Contract
    {
        $this->cntMedElec = $cntMedElec;

        return $this;
    }

    /**
     * Get cntMedElec
     *
     * @return integer
     */
    public function getCntMedElec(): int
    {
        return $this->cntMedElec;
    }

    /**
     * Set medElectrico
     *
     * @param string $medElectrico
     *
     * @return Contract
     */
    public function setMedElectrico($medElectrico): Contract
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    /**
     * Get medElectrico
     *
     * @return string
     */
    public function getMedElectrico(): string
    {
        return $this->medElectrico;
    }

    /**
     * Set medAgua
     *
     * @param string $medAgua
     *
     * @return Contract
     */
    public function setMedAgua($medAgua): Contract
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    /**
     * Get medAgua
     *
     * @return string
     */
    public function getMedAgua(): string
    {
        return $this->medAgua;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Contract
     */
    public function setText($text): Contract
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param UserGender $gender
     *
     * @return Contract
     */
    public function setGender(UserGender $gender): Contract
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return UserGender
     */
    public function getGender(): ?UserGender
    {
        return $this->gender;
    }

    /**
     * @param integer $storeNumber
     *
     * @return Contract
     */
    public function setStoreNumber($storeNumber): Contract
    {
        $this->storeNumber = $storeNumber;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStoreNumber(): int
    {
        return $this->storeNumber;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
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
