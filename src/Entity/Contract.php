<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

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
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $storeNumber = 0;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $inqNombreapellido = '';

    /**
     * @var UserGender
     *
     * @ManyToOne(targetEntity="UserGender")
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $inqCi = '000000000-0';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $destination = '';

    /**
     * @ORM\Column(type="float", precision=10, scale=0)
     */
    private $valAlq = 0;

    /**
     * @ORM\Column(type="float", precision=10, scale=0)
     */
    private $valGarantia = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $date = '0000-00-00 00:00:00';

    /**
     * @ORM\Column(type="integer")
     */
    private $cntLanfort = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntNeon = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntSwitch = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntToma = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntVentana = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntLlaves = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntMedAgua = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $cntMedElec = 0;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $medElectrico = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $medAgua = '';

    /**
     * @ORM\Column(type="text", length=65535, nullable=false)
     */
    private $text;

    /**
     * Contract constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
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
    public function setInqNombreapellido($inqNombreapellido)
    {
        $this->inqNombreapellido = $inqNombreapellido;

        return $this;
    }

    /**
     * Get inqNombreapellido
     *
     * @return string
     */
    public function getInqNombreapellido()
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
    public function setInqCi($inqCi)
    {
        $this->inqCi = $inqCi;

        return $this;
    }

    /**
     * Get inqCi
     *
     * @return string
     */
    public function getInqCi()
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
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return string
     */
    public function getDestination()
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
    public function setValAlq($valAlq)
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
    public function setValGarantia($valGarantia)
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
     * @param \DateTime $date
     *
     * @return Contract
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
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
    public function setCntLanfort($cntLanfort)
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    /**
     * Get cntLanfort
     *
     * @return integer
     */
    public function getCntLanfort()
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
    public function setCntNeon($cntNeon)
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    /**
     * Get cntNeon
     *
     * @return integer
     */
    public function getCntNeon()
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
    public function setCntSwitch($cntSwitch)
    {
        $this->cntSwitch = $cntSwitch;

        return $this;
    }

    /**
     * Get cntSwitch
     *
     * @return integer
     */
    public function getCntSwitch()
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
    public function setCntToma($cntToma)
    {
        $this->cntToma = $cntToma;

        return $this;
    }

    /**
     * Get cntToma
     *
     * @return integer
     */
    public function getCntToma()
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
    public function setCntVentana($cntVentana)
    {
        $this->cntVentana = $cntVentana;

        return $this;
    }

    /**
     * Get cntVentana
     *
     * @return integer
     */
    public function getCntVentana()
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
    public function setCntLlaves($cntLlaves)
    {
        $this->cntLlaves = $cntLlaves;

        return $this;
    }

    /**
     * Get cntLlaves
     *
     * @return integer
     */
    public function getCntLlaves()
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
    public function setCntMedAgua($cntMedAgua)
    {
        $this->cntMedAgua = $cntMedAgua;

        return $this;
    }

    /**
     * Get cntMedAgua
     *
     * @return integer
     */
    public function getCntMedAgua()
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
    public function setCntMedElec($cntMedElec)
    {
        $this->cntMedElec = $cntMedElec;

        return $this;
    }

    /**
     * Get cntMedElec
     *
     * @return integer
     */
    public function getCntMedElec()
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
    public function setMedElectrico($medElectrico)
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    /**
     * Get medElectrico
     *
     * @return string
     */
    public function getMedElectrico()
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
    public function setMedAgua($medAgua)
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    /**
     * Get medAgua
     *
     * @return string
     */
    public function getMedAgua()
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
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param UserGender $gender
     *
     * @return Contract
     */
    public function setGender(UserGender $gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return UserGender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param integer $storeNumber
     *
     * @return Contract
     */
    public function setStoreNumber($storeNumber)
    {
        $this->storeNumber = $storeNumber;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStoreNumber()
    {
        return $this->storeNumber;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
    public function setValuesFromStore(Store $store)
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
