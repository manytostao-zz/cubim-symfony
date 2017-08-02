<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\BibliografiaBundle\Entity;


use BMN\NomencladorBundle\Entity\Nomenclador;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContext;

/**
 * BMN\BibliografiaBundle\Entity\Bibliografia
 *
 * @ORM\Entity(repositoryClass="BMN\BibliografiaBundle\Entity\BibliografiaRepository")
 * @ORM\Table(name="bibliografia")
 */
class Bibliografia
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
     * @ORM\ManyToOne(targetEntity="BMN\AppUserBundle\Entity\AppUser")
     */
    private $appUser;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull(message = "Debe introducir un tema de investigación.")
     */
    private $tema;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $motivo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $fechaSolicitud;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $estilo;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $autoservicio;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $referencia;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $dsi;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull(message = "Debe indicar años de búsqueda.")
     */
    private $fechaDesde;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull(message = "Debe indicar años de búsqueda.")
     */
    private $fechaHasta;

    /**
     * @var
     */
    private $idiomas;

    /**
     * @var
     */
    private $tiposDocs;

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
    public function getIdiomas()
    {
        return $this->idiomas;
    }

    /**
     * @param ArrayCollection $idiomas
     */
    public function setIdiomas($idiomas)
    {
        $this->idiomas = $idiomas;
    }

    /**
     * @return mixed
     */
    public function getTiposDocs()
    {
        return $this->tiposDocs;
    }

    /**
     * @param ArrayCollection $tiposDocs
     */
    public function setTiposDocs($tiposDocs)
    {
        $this->tiposDocs = $tiposDocs;
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
     * @return int
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

    /**
     * @param int $appUser
     */
    public function setAppUser($appUser)
    {
        $this->appUser = $appUser;
    }

    /**
     * @return string
     */
    public function getTema()
    {
        return $this->tema;
    }

    /**
     * @param string $tema
     */
    public function setTema($tema)
    {
        $this->tema = $tema;
    }

    /**
     * @return string
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * @param string $motivo
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
    }

    /**
     * @return \DateTime
     */
    public function getFechaSolicitud()
    {
        return $this->fechaSolicitud;
    }

    /**
     * @param \DateTime $fechaSolicitud
     */
    public function setFechaSolicitud($fechaSolicitud)
    {
        $this->fechaSolicitud = $fechaSolicitud;
    }

    /**
     * @return int
     */
    public function getEstilo()
    {
        return $this->estilo;
    }

    /**
     * @param int $estilo
     */
    public function setEstilo($estilo)
    {
        $this->estilo = $estilo;
    }

    /**
     * @return boolean
     */
    public function isAutoservicio()
    {
        return $this->autoservicio;
    }

    /**
     * @param boolean $autoservicio
     */
    public function setAutoservicio($autoservicio)
    {
        $this->autoservicio = $autoservicio;
    }

    /**
     * @return boolean
     */
    public function isReferencia()
    {
        return $this->referencia;
    }

    /**
     * @param boolean $referencia
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    /**
     * @return boolean
     */
    public function isDsi()
    {
        return $this->dsi;
    }

    /**
     * @param boolean $dsi
     */
    public function setDsi($dsi)
    {
        $this->dsi = $dsi;
    }

    /**
     * @return int
     */
    public function getFechaDesde()
    {
        return $this->fechaDesde;
    }

    /**
     * @param $fechaDesde
     */
    public function setFechaDesde($fechaDesde)
    {
        $this->fechaDesde = $fechaDesde;
    }

    /**
     * @return int
     */
    public function getFechaHasta()
    {
        return $this->fechaHasta;
    }

    /**
     * @param int $fechaHasta
     */
    public function setFechaHasta($fechaHasta)
    {
        $this->fechaHasta = $fechaHasta;
    }

}