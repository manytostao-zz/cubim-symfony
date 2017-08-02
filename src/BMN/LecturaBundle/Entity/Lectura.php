<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\LecturaBundle\Entity;

use BMN\NomencladorBundle\Entity\Nomenclador;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\LecturaBundle\Entity\Lectura
 *
 * @ORM\Entity(repositoryClass="BMN\LecturaBundle\Entity\LecturaRepository")
 * @ORM\Table(name="lectura")
 */
class Lectura
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
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * @ORM\OneToMany(targetEntity="LecturaModalidad", cascade={"persist"}, mappedBy="lectura")
     */
    private $lecturaModalidad;

    public function __construct()
    {
        $this->lecturaModalidad = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getLecturaModalidad()
    {
        return $this->lecturaModalidad;
    }

    public function addLecturaModalidad(LecturaModalidad $lecturaMod)
    {
        $lecturaMod->setLectura($this);
        $this->lecturaModalidad->add($lecturaMod);
    }

    public function removeLecturaModalidad(LecturaModalidad $lecturaMod)
    {
        $this->lecturaModalidad->removeElement($lecturaMod);
    }

//    /**
//     * @param mixed $lecturaModalidad
//     */
//    public function setLecturaModalidad($lecturaModalidad)
//    {
//        $this->lecturaModalidad = $lecturaModalidad;
//    }

    /**
     * @return mixed
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
     * @return int
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param int $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return \DateTime
     */
    public function getEntrada()
    {
        return $this->entrada;
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
    public function getSalida()
    {
        return $this->salida;
    }

    /**
     * @param \DateTime $salida
     */
    public function setSalida($salida)
    {
        $this->salida = $salida;
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