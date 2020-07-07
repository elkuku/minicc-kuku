<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * ORM\Table(name="cc_user")
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class User implements UserInterface, Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @var Store[]
     *
     * @ORM\OneToMany(targetEntity="Store", mappedBy="user")
     */
    private $stores;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $role = 'ROLE_USER';

    /**
     * @var UserGender
     *
     * @ManyToOne(targetEntity="UserGender")
     */
    private UserGender $gender;

    /**
     * User State
     * Active or Inactive
     *
     * @var UserState
     *
     * @ManyToOne(targetEntity="UserState")
     */
    private UserState $state;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $inqCi = '';

    /**
     * @ORM\Column(type="string", length=13, nullable=true)
     */
    private string $inqRuc = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private string $telefono = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private string $telefono2 = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $direccion = '';

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->stores = new ArrayCollection;
    }

    /**
     * {@inheritdoc}
     * @return null
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return (Role|string)[] The user roles
     */
    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSalt(): void
    {
    }

    /**
     * @param UserState $status
     *
     * @return User
     */
    public function setState(UserState $status): User
    {
        $this->state = $status;

        return $this;
    }

    /**
     * @return UserState
     */
    public function getState(): UserState
    {
        return $this->state;
    }

    /**
     * Set inqCi
     *
     * @param string $inqCi
     *
     * @return User
     */
    public function setInqCi($inqCi): User
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
     * Set inqRuc
     *
     * @param string $inqRuc
     *
     * @return User
     */
    public function setInqRuc($inqRuc): User
    {
        $this->inqRuc = $inqRuc;

        return $this;
    }

    /**
     * Get inqRuc
     *
     * @return string
     */
    public function getInqRuc(): string
    {
        return $this->inqRuc;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     *
     * @return User
     */
    public function setTelefono($telefono): User
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono(): string
    {
        return $this->telefono;
    }

    /**
     * Set telefono2
     *
     * @param string $telefono2
     *
     * @return User
     */
    public function setTelefono2($telefono2): User
    {
        $this->telefono2 = $telefono2;

        return $this;
    }

    /**
     * Get telefono2
     *
     * @return string
     */
    public function getTelefono2(): string
    {
        return $this->telefono2;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return User
     */
    public function setDireccion($direccion): User
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getDireccion(): string
    {
        return $this->direccion;
    }

    /**
     * Add store
     *
     * @param Store $store
     *
     * @return User
     */
    public function addStore(Store $store): User
    {
        $this->stores[] = $store;

        return $this;
    }

    /**
     * Remove store
     *
     * @param Store $store
     *
     * @return User
     */
    public function removeStore(Store $store): User
    {
        $this->stores->removeElement($store);

        return $this;
    }

    /**
     * Get stores
     *
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * @param UserGender $gender
     *
     * @return User
     */
    public function setGender(UserGender $gender): User
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return UserGender
     */
    public function getGender(): UserGender
    {
        return $this->gender;
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->email,
            ]
        );
    }

    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        list (
            $this->id,
            $this->email,
            )
            = unserialize($serialized);
    }
}
