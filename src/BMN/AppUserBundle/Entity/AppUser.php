<?php

namespace BMN\AppUserBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BMN\AppUserBundle\Entity\AppUser
 *
 * @ORM\Entity(repositoryClass="BMN\AppUserBundle\Entity\AppUserRepository")
 * @ORM\Table(name="appuser")
 */
class AppUser implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $apellidos;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $fechaAlta;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;

    /**
     * @var
     */
    private $accountNonExpired;

    /**
     * @var
     */
    private $accountNonLocked;

    /**
     * @var
     */
    private $credentialNonExpired;


    /**
     *
     */
    public function __construct()
    {
        $this->fechaAlta = new \DateTime('today', new \DateTimeZone('America/Havana'));
        $this->roles = new ArrayCollection();
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
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return AppUser
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set apellidos
     *
     * @param string $apellidos
     * @return AppUser
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    /**
     * Get apellidos
     *
     * @return string
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return AppUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return AppUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return AppUser
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set fechaAlta
     *
     * @param \DateTime $fechaAlta
     * @return AppUser
     */
    public function setFechaAlta($fechaAlta)
    {
        $this->fechaAlta = $fechaAlta;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActivo()
    {
        return $this->activo;
    }

    /**
     * @param boolean $activo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    /**
     * Get fechaAlta
     *
     * @return \DateTime
     */
    public function getFechaAlta()
    {
        return $this->fechaAlta;
    }

    //From UserInterface
    /**
     *
     */
    function eraseCredentials()
    {
    }

    /**
     * @return array|ArrayCollection
     */
    function getRoles()
    {
        if (!is_array($this->roles))
            return $this->roles->toArray();
        else return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }
    /**
     * @param mixed $accountExpired
     */
    public function setAccountNonExpired($accountExpired)
    {
        $this->accountNonExpired = $accountExpired;
    }

    /**
     * @param mixed $accountlocked
     */
    public function setAccountNonLocked($accountlocked)
    {
        $this->accountNonLocked = $accountlocked;
    }

    /**
     * @param mixed $credentialExpired
     */
    public function setCredentialNonExpired($credentialExpired)
    {
        $this->credentialNonExpired = $credentialExpired;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->activo = $enabled;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNombre() . ' ' . $this->getApellidos();
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        if(is_null($this->accountNonExpired))
            return true;
        return $this->accountNonExpired;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return Boolean true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        if(is_null($this->accountNonLocked))
            return true;
        return $this->accountNonLocked;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        if(is_null($this->credentialNonExpired))
            return true;
        return $this->credentialNonExpired;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return Boolean true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        if (is_null($this->activo))
            return true;

        return $this->activo;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return \json_encode(
            array($this->username, $this->password, $this->salt,
                $this->roles, $this->id));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list($this->username, $this->password, $this->salt,
            $this->roles, $this->id) = \json_decode(
            $serialized);
    }
}
