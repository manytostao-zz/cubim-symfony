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
 * BMN\BibliografiaBundle\Entity\BibliografiaRespuesta
 *
 * @ORM\Entity
 * @ORM\Table(name="bibliografia_respuesta")
 */
class BibliografiaRespuesta
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
     * @ORM\ManyToOne(targetEntity="BMN\BibliografiaBundle\Entity\Bibliografia")
     */
    private $bibliografia;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull(message = "Debe introducir descriptores")
     */
    private $descriptores;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $citasRelevantes;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $citasPertinentes;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotNull(message = "Debe introducir las citas")
     */
    private $citas;

    /**
     *
     * @ORM\ManyToMany(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador", inversedBy="referencias")
     */
    private $fuentesInfo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\AppUserBundle\Entity\AppUser")
     */
    private $appUser;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $observaciones;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $fechaRespuesta;

    /**
     *
     */
    public function __construct()
    {
        $this->fuentesInfo = new ArrayCollection();
    }

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
    public function getBibliografia()
    {
        return $this->bibliografia;
    }

    /**
     * @param mixed $bibliografia
     */
    public function setBibliografia($bibliografia)
    {
        $this->bibliografia = $bibliografia;
    }

    /**
     * @return string
     */
    public function getDescriptores()
    {
        return $this->descriptores;
    }

    /**
     * @param string $descriptores
     */
    public function setDescriptores($descriptores)
    {
        $this->descriptores = $descriptores;
    }

    /**
     * @return int
     */
    public function getCitasRelevantes()
    {
        return $this->citasRelevantes;
    }

    /**
     * @param int $citasRelevantes
     */
    public function setCitasRelevantes($citasRelevantes)
    {
        $this->citasRelevantes = $citasRelevantes;
    }

    /**
     * @return int
     */
    public function getCitasPertinentes()
    {
        return $this->citasPertinentes;
    }

    /**
     * @param int $citasPertinentes
     */
    public function setCitasPertinentes($citasPertinentes)
    {
        $this->citasPertinentes = $citasPertinentes;
    }

    /**
     * @return string
     */
    public function getCitas()
    {
        return $this->citas;
    }

    /**
     * @param string $citas
     */
    public function setCitas($citas)
    {
        $this->citas = $citas;
    }

    /**
     * @return mixed
     */
    public function getFuentesInfo()
    {
        return $this->fuentesInfo;
    }

    /**
     *
     */
    public function restartFuentesInfo()
    {
        $this->fuentesInfo = new ArrayCollection();
    }

    /**
     * @param $fuenteInfo
     */
    public function addFuentesInfo($fuenteInfo)
    {
        if ($fuenteInfo instanceof Nomenclador) {
            $this->fuentesInfo->add($fuenteInfo);
        }
    }

    /**
     * @param Nomenclador $fuenteInfo
     */
    public function removeFuentesInfo(Nomenclador $fuenteInfo)
    {
        $this->fuentesInfo->removeElement($fuenteInfo);
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
     * @return \DateTime
     */
    public function getFechaRespuesta()
    {
        return $this->fechaRespuesta;
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
     * @param \DateTime $fechaRespuesta
     */
    public function setFechaRespuesta($fechaRespuesta)
    {
        $this->fechaRespuesta = $fechaRespuesta;
    }


}