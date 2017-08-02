<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\RecepcionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BMN\RecepcionBundle\Entity\Recepcion
 *
 * @ORM\Entity(repositoryClass="BMN\RecepcionBundle\Entity\RecepcionRepository")
 * @ORM\Table(name="recepcion")
 */

class Recepcion
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $entrada;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $salida;

    /**
     * @var float
     *
     * @ORM\Column(name="chapilla", type="float", nullable=true)
     */
    private $chapilla;

    /**
     * @var string
     *
     * @ORM\Column(name="documento", type="text", nullable=true)
     */
    private $documento;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $chapilla
     */
    public function setChapilla($chapilla)
    {
        $this->chapilla = $chapilla;
    }

    /**
     * @return float
     */
    public function getChapilla()
    {
        return $this->chapilla;
    }

    /**
     * @param \DateTime $entrada
     */
    public function setEntrada($entrada)
    {
        $this->entrada = $entrada;
    }

    /**
     * @return \DateTime
     */
    public function getEntrada()
    {
        return $this->entrada;
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
     * @param \DateTime $salida
     */
    public function setSalida($salida)
    {
        $this->salida = $salida;
    }

    /**
     * @return \DateTime
     */
    public function getSalida()
    {
        return $this->salida;
    }

    /**
     * @return string
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param string $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param string $observaciones
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }

} 