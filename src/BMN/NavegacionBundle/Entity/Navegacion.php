<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\NavegacionBundle\Entity;

use BMN\NomencladorBundle\Entity\Nomenclador;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\NavegacionBundle\Entity\Navegacion
 *
 * @ORM\Entity(repositoryClass="BMN\NavegacionBundle\Entity\NavegacionRepository")
 * @ORM\Table(name="navegacion")
 */
class Navegacion
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
     * @var integer
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $correo;

    /**
     * @var string
     *
     * @ORM\Column(name="necesidad", type="text", nullable=true)
     */
    private $necesidad;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * @var integer
     *
     * @Assert\NotBlank(message="No quedan estaciones de trabajo por asignar")
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $pc;

    /**
     *
     * @ORM\ManyToMany(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador", inversedBy="referencias")
     */
    private $fuentesInfo;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param mixed $usuario
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
     * @return int
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * @param int $correo
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    }

    /**
     * @return float
     */
    public function getNecesidad()
    {
        return $this->necesidad;
    }

    /**
     * @param float $necesidad
     */
    public function setNecesidad($necesidad)
    {
        $this->necesidad = $necesidad;
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

    /**
     * @return int
     */
    public function getPc()
    {
        return $this->pc;
    }

    /**
     * @param int $pc
     */
    public function setPc($pc)
    {
        $this->pc = $pc;
    }

    /**
     * @return mixed
     */
    public function getFuentesInfo()
    {
        return $this->fuentesInfo;
    }

    public function restartFuentesInfo()
    {
        $this->fuentesInfo = new ArrayCollection();
    }

    public function addFuentesInfo($fuenteInfo)
    {
        if ($fuenteInfo instanceof Nomenclador) {
            $this->fuentesInfo->add($fuenteInfo);
        }
    }

    public function removeFuentesInfo(Nomenclador $fuenteInfo)
    {
        $this->fuentesInfo->removeElement($fuenteInfo);
    }

    public function __construct()
    {

        $this->fuentesInfo = new ArrayCollection();
    }
} 