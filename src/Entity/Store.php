<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoreRepository")
 */
class Store
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $userId = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $destination = '';

    /**
     * @var float
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=false)
     */
    private float $valAlq = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntLanfort = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntNeon = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntSwitch = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntToma = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntVentana = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntLlaves = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntMedAgua = 0;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntMedElec = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private string $medElectrico = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private string $medAgua = '';

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="stores")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

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
     * Set idUser
     *
     * @param integer $idUser
     *
     * @return Store
     */
    public function setUserId($idUser): Store
    {
        $this->userId = $idUser;

        return $this;
    }

    /**
     * Get idUser
     *
     * @return integer
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set destination
     *
     * @param string $destination
     *
     * @return Store
     */
    public function setDestination($destination): Store
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
     * @return Store
     */
    public function setValAlq($valAlq): Store
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    /**
     * Get valAlq
     *
     * @return float
     */
    public function getValAlq(): float
    {
        return $this->valAlq;
    }

    /**
     * Set cntLanfort
     *
     * @param integer $cntLanfort
     *
     * @return Store
     */
    public function setCntLanfort($cntLanfort): Store
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
     * @return Store
     */
    public function setCntNeon($cntNeon): Store
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
     * @return Store
     */
    public function setCntSwitch($cntSwitch): Store
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
     * @return Store
     */
    public function setCntToma($cntToma): Store
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
     * @return Store
     */
    public function setCntVentana($cntVentana): Store
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
     * @return Store
     */
    public function setCntLlaves($cntLlaves): Store
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
     * @return Store
     */
    public function setCntMedAgua($cntMedAgua): Store
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
     * @return Store
     */
    public function setCntMedElec($cntMedElec): Store
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
     * @param integer $medElectrico
     *
     * @return Store
     */
    public function setMedElectrico($medElectrico): Store
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    public function getMedElectrico(): string
    {
        return $this->medElectrico;
    }

    /**
     * Set medAgua
     *
     * @param integer $medAgua
     *
     * @return Store
     */
    public function setMedAgua($medAgua): Store
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    public function getMedAgua(): string
    {
        return $this->medAgua;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Store
     */
    public function setUser(User $user): Store
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
