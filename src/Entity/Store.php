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
 * Store
 *
 * @ORM\Table(name="store")
 * @ORM\Entity(repositoryClass="App\Repository\StoreRepository")
 */
class Store
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="user_id", type="integer", nullable=true)
	 */
	private $userId = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="destination", type="string", length=50, nullable=false)
	 */
	private $destination = '';

	/**
	 * @var float
	 *
	 * @ORM\Column(name="val_alq", type="float", precision=10, scale=0, nullable=false)
	 */
	private $valAlq = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_lanfort", type="integer", nullable=false)
	 */
	private $cntLanfort = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_neon", type="integer", nullable=false)
	 */
	private $cntNeon = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_switch", type="integer", nullable=false)
	 */
	private $cntSwitch = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_toma", type="integer", nullable=false)
	 */
	private $cntToma = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_ventana", type="integer", nullable=false)
	 */
	private $cntVentana = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_llaves", type="integer", nullable=false)
	 */
	private $cntLlaves = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_med_agua", type="integer", nullable=false)
	 */
	private $cntMedAgua = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="cnt_med_elec", type="integer", nullable=false)
	 */
	private $cntMedElec = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="med_electrico", type="string", length=50)
	 */
	private $medElectrico;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="med_agua", type="string", length=50)
	 */
	private $medAgua = 0;

	/**
	 * @var User
	 *
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="stores")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $user;

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
	 * Set idUser
	 *
	 * @param integer $idUser
	 *
	 * @return Store
	 */
	public function setUserId($idUser)
	{
		$this->userId = $idUser;

		return $this;
	}

	/**
	 * Get idUser
	 *
	 * @return integer
	 */
	public function getUserId()
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
	 * @return Store
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
	 * Set cntLanfort
	 *
	 * @param integer $cntLanfort
	 *
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @return Store
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
	 * @param integer $medElectrico
	 *
	 * @return Store
	 */
	public function setMedElectrico($medElectrico)
	{
		$this->medElectrico = $medElectrico;

		return $this;
	}

	/**
	 * Get medElectrico
	 *
	 * @return integer
	 */
	public function getMedElectrico()
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
	public function setMedAgua($medAgua)
	{
		$this->medAgua = $medAgua;

		return $this;
	}

	/**
	 * Get medAgua
	 *
	 * @return integer
	 */
	public function getMedAgua()
	{
		return $this->medAgua;
	}

	/**
	 * Set user
	 *
	 * @param \App\Entity\User $user
	 *
	 * @return Store
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return \App\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
