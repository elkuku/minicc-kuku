<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * ORM\Table(name="cc_user")
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $role = 'ROLE_USER';

    /**
     * @var string
     *
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @var UserGender
     *
     * @ManyToOne(targetEntity="UserGender")
     */
    private $gender;

    /**
     * User State
     * Active or Inactive
     *
     * @var UserState
     *
     * @ManyToOne(targetEntity="UserState")
     */
    private $state;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabled = true;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $inqCi = '';

    /**
     * @ORM\Column(type="string", length=13, nullable=false)
     */
    private $inqRuc = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     */
    private $telefono = '';

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     */
    private $telefono2 = '';

    /**
     * @ORM\Column(type="string", length=250, nullable=false)
     */
    private $direccion = '';

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
    public function eraseCredentials()
    {
        return null;
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
    public function setRole(string $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return [$this->getRole()];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * getName
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * getPlainPassword
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * setPlainPassword
     */
    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @param UserState $status
     *
     * @return User
     */
    public function setState(UserState $status)
    {
        $this->state = $status;

        return $this;
    }

    /**
     * @return UserState
     */
    public function getState()
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
     * Set inqRuc
     *
     * @param string $inqRuc
     *
     * @return User
     */
    public function setInqRuc($inqRuc)
    {
        $this->inqRuc = $inqRuc;

        return $this;
    }

    /**
     * Get inqRuc
     *
     * @return string
     */
    public function getInqRuc()
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
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono()
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
    public function setTelefono2($telefono2)
    {
        $this->telefono2 = $telefono2;

        return $this;
    }

    /**
     * Get telefono2
     *
     * @return string
     */
    public function getTelefono2()
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
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Add store
     *
     * @param \App\Entity\Store $store
     *
     * @return User
     */
    public function addStore(Store $store)
    {
        $this->stores[] = $store;

        return $this;
    }

    /**
     * Remove store
     *
     * @param \App\Entity\Store $store
     *
     * @return User
     */
    public function removeStore(Store $store)
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
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->password,
                $this->isEnabled,
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
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isEnabled,
            )
            = unserialize($serialized);
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param boolean $isEnabled
     *
     * @return User
     */
    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}
