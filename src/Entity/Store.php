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
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $destination = '';

    /**
     * @ORM\Column(type="float", precision=10, scale=0, nullable=false)
     */
    private float $valAlq = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntLanfort = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntNeon = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntSwitch = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntToma = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntVentana = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntLlaves = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $cntMedAgua = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="stores")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUserId(int $idUser): Store
    {
        $this->userId = $idUser;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setDestination(string $destination): Store
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setValAlq($valAlq): Store
    {
        $this->valAlq = $valAlq;

        return $this;
    }

    public function getValAlq(): float
    {
        return $this->valAlq;
    }

    public function setCntLanfort(int $cntLanfort): Store
    {
        $this->cntLanfort = $cntLanfort;

        return $this;
    }

    public function getCntLanfort(): int
    {
        return $this->cntLanfort;
    }

    public function setCntNeon(int $cntNeon): Store
    {
        $this->cntNeon = $cntNeon;

        return $this;
    }

    public function getCntNeon(): int
    {
        return $this->cntNeon;
    }

    public function setCntSwitch(int $cntSwitch): Store
    {
        $this->cntSwitch = $cntSwitch;

        return $this;
    }

    public function getCntSwitch(): int
    {
        return $this->cntSwitch;
    }

    public function setCntToma(int $cntToma): Store
    {
        $this->cntToma = $cntToma;

        return $this;
    }

    public function getCntToma(): int
    {
        return $this->cntToma;
    }

    public function setCntVentana(int $cntVentana): Store
    {
        $this->cntVentana = $cntVentana;

        return $this;
    }

    public function getCntVentana(): int
    {
        return $this->cntVentana;
    }

    public function setCntLlaves(int $cntLlaves): Store
    {
        $this->cntLlaves = $cntLlaves;

        return $this;
    }

    public function getCntLlaves(): int
    {
        return $this->cntLlaves;
    }

    public function setCntMedAgua(int $cntMedAgua): Store
    {
        $this->cntMedAgua = $cntMedAgua;

        return $this;
    }

    public function getCntMedAgua(): int
    {
        return $this->cntMedAgua;
    }

    public function setCntMedElec(int $cntMedElec): Store
    {
        $this->cntMedElec = $cntMedElec;

        return $this;
    }

    public function getCntMedElec(): int
    {
        return $this->cntMedElec;
    }

    public function setMedElectrico(int $medElectrico): Store
    {
        $this->medElectrico = $medElectrico;

        return $this;
    }

    public function getMedElectrico(): string
    {
        return $this->medElectrico;
    }

    public function setMedAgua(int $medAgua): Store
    {
        $this->medAgua = $medAgua;

        return $this;
    }

    public function getMedAgua(): string
    {
        return $this->medAgua;
    }

    public function setUser(?User $user): Store
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
