<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\DSIBundle\Entity;

use BMN\NomencladorBundle\Entity\Nomenclador;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContext;

/**
 * BMN\DSIBundle\Entity\DSI
 *
 * @ORM\Entity(repositoryClass="BMN\DSIBundle\Entity\DSIRepository")
 * @ORM\Table(name="dsi")
 * @ORM\HasLifecycleCallbacks
 * @Assert\Callback(methods={"nameNotNull"})
 */
class DSI
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
     * @ORM\Column(type="string")
     */
    private $pregunta;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaSolicitud;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRespuesta;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $viaSolicitud;

    /**
     *
     * @ORM\ManyToMany(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador", inversedBy="referencias")
     */
    private $fuentesInfo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $respuesta;

    /**
     * @var integer
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $documento;

    /**
     * @var integer
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $referencia;

    /**
     * @var integer
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $desiderata;

    /**
     * @var integer
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $verbal;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="path", type="text", length=255, nullable=true)
     */
    private $path;

    /**
     * Image file
     *
     * @var File
     *
     * @Assert\File(
     *     maxSize = "20M",
     *     mimeTypes = {"application/x-rar-compressed", "application/pdf", "application/msword", "application/zip", "image/jpeg", "image/gif", "image/png"},
     *     maxSizeMessage = "El tamaño máximo del fuchero es de 20MB.",
     *     mimeTypesMessage = "Solo puede adjuntar archivos con extensión .zip, .jpeg, .png, .doc"
     * )
     */
    private $file;

    private $temp;


    public function __construct()
    {
        $this->fuentesInfo = new ArrayCollection();
    }

    #region File
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }


    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }


    public function getAbsolutePath()
    {
        return (null === $this->path or '' === $this->path)
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
// the absolute directory path where uploaded
// documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
// get rid of the __DIR__ so it doesn't screw up
// when displaying uploaded doc/image in the view.
        return '/reference/attachments';
    }

    /**
     * Called before saving the entity
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name

            $filename = $this->name;
            $this->path = $filename . '.' . $this->file->guessExtension();
        }
    }

    /**
     * Called before entity removal
     *
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {

            unlink($file);
        }
    }

    /**
     * Called after entity persistence
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // The file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        // Use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to

        $this->file->move(

            $this->getUploadRootDir(),
            $this->path
        );
        // check if we have an old image
        if (isset($this->temp) && $this->temp != "") {
            // delete the old image
            // unlink($this->getUploadRootDir() . '/' . $this->temp);
            // clear the temp image path
            $this->temp = null;
        }

        // Clean up the file property as you won't need it anymore

        $this->file = null;
    }
    #endregion

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return \DateTime
     */
    public function getFechaRespuesta()
    {
        return $this->fechaRespuesta;
    }

    /**
     * @param \DateTime $fechaRespuesta
     */
    public function setFechaRespuesta($fechaRespuesta)
    {
        $this->fechaRespuesta = $fechaRespuesta;
    }

    /**
     * @return mixed
     */
    public function getFuentesInfo()
    {
        return $this->fuentesInfo;
    }

    /**
     * @return string
     */
    public function getPregunta()
    {
        return $this->pregunta;
    }

    /**
     * @param string $pregunta
     */
    public function setPregunta($pregunta)
    {
        $this->pregunta = $pregunta;
    }

    /**
     * @return string
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * @param string $respuesta
     */
    public function setRespuesta($respuesta)
    {
        $this->respuesta = $respuesta;
    }

    /**
     * @return int
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param int $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return int
     */
    public function getReferencia()
    {
        return $this->referencia;
    }

    /**
     * @param int $referencia
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    /**
     * @return int
     */
    public function getVerbal()
    {
        return $this->verbal;
    }

    /**
     * @param int $verbal
     */
    public function setVerbal($verbal)
    {
        $this->verbal = $verbal;
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
    public function getViaSolicitud()
    {
        return $this->viaSolicitud;
    }

    /**
     * @param int $viaSolicitud
     */
    public function setViaSolicitud($viaSolicitud)
    {
        $this->viaSolicitud = $viaSolicitud;
    }

    /**
     * @return int
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

    /**
     * @internal param mixed $id
     */
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

    /**
     * @param int $appUser
     */
    public function setAppUser($appUser)
    {
        $this->appUser = $appUser;
    }

    /**
     * @return int
     */
    public function getDesiderata()
    {
        return $this->desiderata;
    }

    /**
     * @param int $desiderata
     */
    public function setDesiderata($desiderata)
    {
        $this->desiderata = $desiderata;
    }

    public function nameNotNull(ExecutionContext $context)
    {
        if (!is_null($this->getFile()) && is_null($this->getName())) {
            $context->addViolationAt(
                'name',
                'Debe escoger un nombre para el archivo adjunto',
                array(),
                null
            );


        }

        $pattern = '/[^A-Za-z0-9]/';
        if (!is_null($this->getFile()) && preg_match($pattern, $this->getName())) {
            $context->addViolationAt(
                'name',
                'El nombre del adjunto debe estar formado por una combinación de números y letras sin tildes ',
                array(),
                null
            );
        }

        return;
    }

}