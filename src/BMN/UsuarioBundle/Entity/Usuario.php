<?php

namespace BMN\UsuarioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ExecutionContext;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="BMN\UsuarioBundle\Entity\UsuarioRepository")
 * @UniqueEntity(fields = {"carnetId"}, message="Este Carnet de Identidad ya está siendo usado")
 * @UniqueEntity(fields = {"carnetBib", "tipoUsua"}, message="Este número de carnet ya ha sido usado en otro usuario.")
 * @UniqueEntity(fields = {"nombres", "apellidos"}, message="Ya existe un usuario con ese nombre y apellidos")
 * @Assert\Callback(methods={"profNotNull", "validCarnetId", "carreraEst", "carnetBibNotNull"})
 *
 */
class Usuario
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="carnetBib", type="decimal", nullable=true)
     */
    private $carnetBib;

    /**
     * @var float
     *
     * @ORM\Column(name="carnetId", type="string", nullable=true)
     */
    private $carnetId;

    /**
     * @var string
     *
     * @ORM\Column(name="nombres", type="string", length=255, nullable=true)
     * @Assert\NotNull(message = "Debe introducir un nombre.")
     */
    private $nombres;

    /**
     * @var string
     *
     * @ORM\Column(name="apellidos", type="string", length=255)
     * @Assert\NotNull(message = "Debe introducir apellidos.")
     */
    private $apellidos;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $pais;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     *
     * @Assert\Email(message="El Correo Electrónico no es válido.")
     */
    private $email;

    /**
     * @var float
     *
     * @ORM\Column(name="telefono", type="decimal", nullable=true)
     */
    private $telefono;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $tipoUsua;

    /**
     * @var boolean
     *
     * @ORM\Column(name="estudiante", type="boolean", nullable=true)
     */
    private $estudiante;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $tipoPro;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $especialidad;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $profesion;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $categOcup;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $categCien;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $categInv;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $categDoc;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $cargo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $institucion;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $dedicacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="experiencia", type="integer", nullable=true)
     */
    private $experiencia;

    /**
     * @var integer
     *
     * @ORM\Column(name="fechaIns", type="date", nullable=true)
     */
    private $fechaIns;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;

    /**
     * @var string
     *
     * @ORM\Column(name="temaInv", type="text", nullable=true)
     */
    private $temaInv;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\AppUserBundle\Entity\AppUser")
     */
    private $atendidoPor;

    /**
     * @var string
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var string
     *
     * @ORM\Column(name="banned", type="boolean", nullable=true)
     */
    private $banned;


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
     * Set carnetBib
     *
     * @param float $carnetBib
     * @return Usuario
     */
    public function setCarnetBib($carnetBib)
    {
        $this->carnetBib = $carnetBib;

        return $this;
    }

    /**
     * Get carnetBib
     *
     * @return float
     */
    public function getCarnetBib()
    {
        return $this->carnetBib;
    }

    /**
     * Set carnetId
     *
     * @param float $carnetId
     * @return Usuario
     */
    public function setCarnetId($carnetId)
    {
        $this->carnetId = $carnetId;

        return $this;
    }

    /**
     * Get carnetId
     *
     * @return float
     */
    public function getCarnetId()
    {
        return $this->carnetId;
    }

    /**
     * Set nombres
     *
     * @param string $nombres
     * @return Usuario
     */
    public function setNombres($nombres)
    {
        $this->nombres = $nombres;

        return $this;
    }

    /**
     * Get nombres
     *
     * @return string
     */
    public function getNombres()
    {
        return $this->nombres;
    }

    /**
     * Set apellidos
     *
     * @param string $apellidos
     * @return Usuario
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
     * @param int $pais
     */
    public function setPais($pais)
    {
        $this->pais = $pais;
    }

    /**
     * @return int
     */
    public function getPais()
    {
        return $this->pais;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telefono
     *
     * @param float $telefono
     * @return Usuario
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return float
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set tipoUsua
     *
     * @param $tipoUsua
     * @return Usuario
     */
    public function setTipoUsua($tipoUsua)
    {
        $this->tipoUsua = $tipoUsua;

        return $this;
    }

    /**
     * Get tipoUsua
     *
     * @return integer
     */
    public function getTipoUsua()
    {
        return $this->tipoUsua;
    }

    /**
     * @param boolean $estudiante
     */
    public function setEstudiante($estudiante)
    {
        $this->estudiante = $estudiante;
    }

    /**
     * @return boolean
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set tipoPro
     *
     * @param $tipoPro
     * @return Usuario
     */
    public function setTipoPro($tipoPro)
    {
        $this->tipoPro = $tipoPro;

        return $this;
    }

    /**
     * Get tipoPro
     *
     * @return integer
     */
    public function getTipoPro()
    {
        return $this->tipoPro;
    }

    /**
     * Set especialidad
     *
     * @param $especialidad
     * @return Usuario
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    /**
     * Get especialidad
     *
     * @return integer
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set profesion
     *
     * @param $profesion
     * @return Usuario
     */
    public function setProfesion($profesion)
    {
        $this->profesion = $profesion;

        return $this;
    }

    /**
     * Get profesion
     *
     * @return integer
     */
    public function getProfesion()
    {
        return $this->profesion;
    }

    /**
     * Set categOcup
     *
     * @param $categOcup
     * @return Usuario
     */
    public function setCategOcup($categOcup)
    {

        $this->categOcup = $categOcup;


        return $this;
    }

    /**
     * Get categOcup
     *
     * @return integer
     */
    public function getCategOcup()
    {
        return $this->categOcup;
    }

    /**
     * Set categCien
     *
     * @param $categCien
     * @return Usuario
     */
    public function setCategCien($categCien)
    {
        $this->categCien = $categCien;

        return $this;
    }

    /**
     * Get categCien
     *
     * @return integer
     */
    public function getCategCien()
    {
        return $this->categCien;
    }

    /**
     * Set categInv
     *
     * @param $categInv
     * @return Usuario
     */
    public function setCategInv($categInv)
    {
        $this->categInv = $categInv;

        return $this;
    }

    /**
     * Get categInv
     *
     * @return integer
     */
    public function getCategInv()
    {
        return $this->categInv;
    }

    /**
     * Set categDoc
     *
     * @param $categDoc
     * @return Usuario
     */
    public function setCategDoc($categDoc)
    {
        $this->categDoc = $categDoc;

        return $this;
    }

    /**
     * Get categDoc
     *
     * @return integer
     */
    public function getCategDoc()
    {
        return $this->categDoc;
    }

    /**
     * Set cargo
     *
     * @param $cargo
     * @return Usuario
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;

        return $this;
    }

    /**
     * Get cargo
     *
     * @return integer
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set institucion
     *
     * @param $institucion
     * @return Usuario
     */
    public function setInstitucion($institucion)
    {
        $this->institucion = $institucion;

        return $this;
    }

    /**
     * Get institucion
     *
     * @return integer
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * Set dedicacion
     *
     * @param $dedicacion
     * @return Usuario
     */
    public function setDedicacion($dedicacion)
    {
        $this->dedicacion = $dedicacion;

        return $this;
    }

    /**
     * Get dedicacion
     *
     * @return integer
     */
    public function getDedicacion()
    {
        return $this->dedicacion;
    }

    /**
     * Set experiencia
     *
     * @param integer $experiencia
     * @return Usuario
     */
    public function setExperiencia($experiencia)
    {
        $this->experiencia = $experiencia;

        return $this;
    }

    /**
     * Get experiencia
     *
     * @return integer
     */
    public function getExperiencia()
    {
        return $this->experiencia;
    }

    /**
     * Set fechaIns
     *
     * @param date $fechaIns
     * @return Usuario
     */
    public function setFechaIns($fechaIns)
    {
        $this->fechaIns = $fechaIns;

        return $this;
    }

    /**
     * Get fechaIns
     *
     * @return date
     */
    public function getFechaIns()
    {
        return $this->fechaIns;
    }

    /**
     * @param int $observaciones
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }

    /**
     * @return int
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param int $temaInv
     */
    public function setTemaInv($temaInv)
    {
        $this->temaInv = $temaInv;
    }

    /**
     * @return int
     */
    public function getTemaInv()
    {
        return $this->temaInv;
    }

    /**
     * @param int $atendidoPor
     */
    public function setAtendidoPor($atendidoPor)
    {
        $this->atendidoPor = $atendidoPor;
    }

    /**
     * @return int
     */
    public function getAtendidoPor()
    {
        return $this->atendidoPor;
    }

    /**
     * @return string
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param string $activo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    /**
     * @param string $banned
     */
    public function setBanned($banned)
    {
        $this->banned = $banned;
    }

    /**
     * @return string
     */
    public function getBanned()
    {
        return $this->banned;
    }

    /**
     * @param $propertyName
     * @return float|null|string
     */
    public function get($propertyName){
        switch($propertyName){
            case "nombres": return $this->getNombres(); break;
            case "apellidos": return $this->getApellidos(); break;
            case "institucion": return !is_null($this->getInstitucion())?$this->getInstitucion()->__toString():null; break;
            case "carnetBib": return $this->getCarnetBib(); break;
            case "fechaIns": return ''/*$this->getFechaIns()*/; break;
        }
    }

    /**
     * @param ExecutionContext $context
     */
    public function profNotNull(ExecutionContext $context)
    {
        if (is_null($this->getEstudiante()) || $this->getEstudiante() == false) {
            if (((!is_null($this->getTipoPro())) && ($this->getTipoPro()->getDescripcion(
                        ) == 'Otros Profesionales y Especialidades')) && (is_null(
                    $this->getProfesion()
                ))
            ) {
                $context->addViolationAt(
                    'profesion',
                    'Si el usuario no es Médico o Estomatólogo, debe escoger una Profesión.',
                    array(),
                    null
                );

                return;
            }
            if (((!is_null($this->getTipoPro())) && (($this->getTipoPro()->getDescripcion(
                            ) != 'Médico') && ($this->getTipoPro()->getDescripcion() != 'Estomatólogo')
                        && ($this->getTipoPro()->getDescripcion() != 'Residente'))) && (!is_null(
                    $this->getEspecialidad()
                ))
            ) {
                $context->addViolationAt(
                    'especialidad',
                    'Si el usuario posee Especialidad, debe ser Médico, Estomatólogo o Residente.',
                    array(),
                    null
                );

                return;
            }
        }

        return;
    }

    /**
     * @param ExecutionContext $context
     */
    public function validCarnetId(ExecutionContext $context)
    {
        $carnetId = $this->getCarnetId();
        $out = array();

        // Comprobar que el formato sea correcto
        if (!is_null($carnetId)) {
            if (((preg_match("/\d{6}/", $carnetId, $out) !== 1) || strcasecmp($carnetId, $out[0]) > 0) &&
                (((preg_match("/\d{11}/", $carnetId, $out) !== 1) || strcasecmp($carnetId, $out[0]) > 0))
            ) {
                $context->addViolationAt(
                    'carnetId',
                    'El número de identidad debe tener 11 dígitos (6 en el caso de los militares).',
                    array(),
                    null
                );

                return;
            }
        }
    }

    /**
     * @param ExecutionContext $context
     */
    public function carreraEst(ExecutionContext $context)
    {
        if (!is_null($this->getEstudiante()) && $this->getEstudiante() == true) {
            if (!is_null($this->getTipoPro()) && (!is_null($this->getProfesion()))
            ) {
                $context->addViolationAt(
                    'estudiante',
                    'Debe escoger un solo tipo de carrera para los estudiantes.',
                    array(),
                    null
                );
            }

            return;

        }
    }

    /**
     * @param ExecutionContext $context
     */
    public function carnetBibNotNull(ExecutionContext $context)
    {
        if (!is_null($this->getTipoUsua()) && is_null($this->getCarnetBib())) {
            {
                $context->addViolationAt(
                    'carnetBib',
                    'Debe escoger un número de Carnet de Usuario para este Tipo de Usuario',
                    array(),
                    null
                );

                return;
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNombres();
    }
}
