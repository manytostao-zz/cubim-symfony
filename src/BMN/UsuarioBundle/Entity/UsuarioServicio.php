<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\UsuarioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BMN\UsuarioBundle\Entity\Usuario_Servicio
 *
 * @ORM\Entity()
 * @ORM\Table(name="usuario_servicio")
 */

class UsuarioServicio
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\UsuarioBundle\Entity\Usuario")
     */
    private $usuario;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $servicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $actual;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @param int $servicio
     */
    public function setServicio($servicio)
    {
        $this->servicio = $servicio;
    }

    /**
     * @return int
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * @param int $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return int
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @return boolean
     */
    public function isActual()
    {
        return $this->actual;
    }

    /**
     * @param boolean $actual
     */
    public function setActual($actual)
    {
        $this->actual = $actual;
    }

} 