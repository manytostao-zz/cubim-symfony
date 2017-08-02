<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\OtrosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BMN\AppUserBundle\Entity\Traza
 *
 * @ORM\Entity(repositoryClass="BMN\OtrosBundle\Entity\TrazaRepository")
 * @ORM\Table(name="traza")
 */

class Traza
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
    private $operacion;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $objeto;

    /**
     * @var integer
     *
     * @ORM\Column(type="string", length=255)
     */
    private $appUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * @var string
     *
     * @ORM\Column(name="modulo", type="string", length=100, nullable=true)
     */
    private $modulo;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $appUser
     */
    public function setAppUser($appUser)
    {
        $this->appUser = $appUser;
    }

    /**
     * @return mixed
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

    /**
     * @param \DateTime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param string $observaciones
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }

    /**
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $operacion
     */
    public function setOperacion($operacion)
    {
        $this->operacion = $operacion;
    }

    /**
     * @return mixed
     */
    public function getOperacion()
    {
        return $this->operacion;
    }

    /**
     * @param string $objeto
     */
    public function setObjeto($objeto)
    {
        $this->objeto = $objeto;
    }

    /**
     * @return string
     */
    public function getObjeto()
    {
        return $this->objeto;
    }

    /**
     * @return string
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    /**
     * @param string $modulo
     */
    public function setModulo($modulo)
    {
        $this->modulo = $modulo;
    }

} 