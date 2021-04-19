<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\PersistentCollection;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
    private ?int $id = null;

    /**
     * @var Store[]
     *
     * @ORM\OneToMany(targetEntity="Store", mappedBy="user")
     */
    private array|ArrayCollection|PersistentCollection $stores;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\NotBlank
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $role = 'ROLE_USER';

    /**
     * @ManyToOne(targetEntity="UserGender")
     */
    private ?UserGender $gender = null;

    /**
     * User State
     * Active or Inactive
     *
     * @ManyToOne(targetEntity="UserState")
     */
    private ?UserState $state = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Assert\NotBlank
     */
    private string $inqCi = '';

    /**
     * @ORM\Column(type="string", length=13, nullable=true)
     */
    private ?string $inqRuc = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private ?string $telefono = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private ?string $telefono2 = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $direccion = '';

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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): void
    {
    }

    public function getSalt(): void
    {
    }

    public function setState(UserState $status): User
    {
        $this->state = $status;

        return $this;
    }

    public function getState(): ?UserState
    {
        return $this->state;
    }

    public function setInqCi(string $inqCi): User
    {
        $this->inqCi = $inqCi;

        return $this;
    }

    public function getInqCi(): string
    {
        return $this->inqCi;
    }

    public function setInqRuc(?string $inqRuc): User
    {
        $this->inqRuc = $inqRuc;

        return $this;
    }

    public function getInqRuc(): string
    {
        return $this->inqRuc;
    }

    public function setTelefono(?string $telefono): User
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono2(?string $telefono2): User
    {
        $this->telefono2 = $telefono2;

        return $this;
    }

    public function getTelefono2(): ?string
    {
        return $this->telefono2;
    }

    public function setDireccion(?string $direccion): User
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function addStore(Store $store): User
    {
        $this->stores[] = $store;

        return $this;
    }

    public function removeStore(Store $store): User
    {
        $this->stores->removeElement($store);

        return $this;
    }

    /**
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }

    public function setGender(UserGender $gender): User
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): ?UserGender
    {
        return $this->gender;
    }

    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->email,
            ]
        );
    }

    public function unserialize($data): void
    {
        [
            $this->id,
            $this->email,
        ]
            = unserialize($data);
    }
}
