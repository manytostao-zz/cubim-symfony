<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 12/10/15
 * Time: 13:54
 */

namespace BMN\LecturaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\LecturaBundle\Entity\LecturaModalidad
 *
 * @ORM\Entity()
 * @ORM\Table(name="lectura_modalidad")
 * @Assert\Callback(methods={"validLibro"})
 */

class LecturaModalidad
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
     * @ORM\ManyToOne(targetEntity="BMN\LecturaBundle\Entity\Lectura")
     */
    private $lectura;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $modalidad;


    /**
     * @ORM\OneToMany(targetEntity="ModalidadDetalle", cascade={"persist"}, mappedBy="lecturaModalidad")
     */
    private $modalidadDetalle;

    /**
     * LecturaModalidad constructor.
     */
    public function __construct()
    {
        $this->modalidadDetalle = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getModalidadDetalle()
    {
        return $this->modalidadDetalle;
    }

    /**
     * @param ModalidadDetalle $modalidadDeta
     */
    public function addModalidadDetalle(ModalidadDetalle $modalidadDeta)
    {
        $modalidadDeta->setLecturaModalidad($this);
        $this->modalidadDetalle->add($modalidadDeta);
    }

    /**
     * @param ModalidadDetalle $modalidadDeta
     */
    public function removeModalidadDetalle(ModalidadDetalle $modalidadDeta)
    {
        $this->modalidadDetalle->removeElement($modalidadDeta);
    }

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
    public function getLectura()
    {
        return $this->lectura;
    }

    /**
     * @param int $lectura
     */
    public function setLectura($lectura)
    {
        $this->lectura = $lectura;
    }

    /**
     * @return int
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * @param int $modalidad
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    }
}