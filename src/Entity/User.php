<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\PersistentCollection;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

#[UniqueEntity(fields: 'email', message: 'This email address is already in use')]
#[Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[Id, GeneratedValue(strategy: 'AUTO')]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[NotBlank]
    #[Column(type: Types::STRING, length: 255)]
    private string $identifier;

    /**
     * @var PersistentCollection<int, Store>|ArrayCollection<int, Store> $stores
     */
    #[OneToMany(mappedBy: 'user', targetEntity: Store::class)]
    private PersistentCollection|ArrayCollection $stores;

    #[NotBlank]
    #[Email(message: "The email '{{ value }}' is not a valid email.")]
    #[Column(type: Types::STRING, length: 255, unique: true)]
    private string $email;

    #[NotBlank]
    #[Column(type: Types::STRING, length: 40)]
    private ?string $name = null;

    #[Column(type: Types::STRING, length: 50)]
    private string $role = 'ROLE_USER';

    #[ManyToOne(targetEntity: UserGender::class)]
    private ?UserGender $gender = null;

    /**
     * Active or Inactive
     */
    #[ManyToOne(targetEntity: UserState::class)]
    private ?UserState $state = null;

    #[NotBlank]
    #[Column(type: Types::STRING, length: 50, nullable: false)]
    private string $inqCi = '';

    #[Column(type: Types::STRING, length: 13, nullable: true)]
    private ?string $inqRuc = '';

    #[Column(type: Types::STRING, length: 25, nullable: true)]
    private ?string $telefono = '';

    #[Column(type: Types::STRING, length: 25, nullable: true)]
    private ?string $telefono2 = '';

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $direccion = '';

    #[Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $googleId = '';

    public final const ROLES
        = [
            'user'  => 'ROLE_USER',
            'admin' => 'ROLE_ADMIN',
        ];

    public function __construct()
    {
        $this->stores = new ArrayCollection;
    }

    /**
     * {@inheritdoc}
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

    /**
     * @return string[]
     */
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

    public function getUserIdentifier(): string
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

    public function setState(UserState $status): static
    {
        $this->state = $status;

        return $this;
    }

    public function getState(): ?UserState
    {
        return $this->state;
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

    public function setInqRuc(?string $inqRuc): static
    {
        $this->inqRuc = $inqRuc;

        return $this;
    }

    public function getInqRuc(): ?string
    {
        return $this->inqRuc;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono2(?string $telefono2): static
    {
        $this->telefono2 = $telefono2;

        return $this;
    }

    public function getTelefono2(): ?string
    {
        return $this->telefono2;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function addStore(Store $store): static
    {
        $this->stores->add($store);

        return $this;
    }

    public function removeStore(Store $store): static
    {
        $this->stores->removeElement($store);

        return $this;
    }

    /**
     * @return PersistentCollection<int, Store>|ArrayCollection<int, Store>
     */
    public function getStores(): PersistentCollection|ArrayCollection
    {
        return $this->stores;
    }

    public function setGender(UserGender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): ?UserGender
    {
        return $this->gender;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array{ id: integer|null, email: string|null}
     */
    #[ArrayShape(['id' => "int|null", 'email' => "string"])]
    public function __serialize(): array
    {
        return
            [
                'id'    => $this->id,
                'email' => $this->email,
            ];
    }

    /**
     * @param array{ id: int|null, email: string|null} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?: null;
        $this->email = $data['email'] ?: null;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }
}
