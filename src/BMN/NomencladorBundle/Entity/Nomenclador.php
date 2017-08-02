<?php

namespace BMN\NomencladorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Nomenclador
 *
 * @ORM\Table(name="nomenclador")
 * @ORM\Entity(repositoryClass="BMN\NomencladorBundle\Entity\NomencladorRepository")
 * @UniqueEntity(fields = {"descripcion", "tiponom"}, message="Esta descripción ya está siendo usada por otro valor del mismo nomenclador.")
 */
class Nomenclador
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255)
     * @Assert\NotNull(message = "Debe introducir una descripción.")
     */
    private $descripcion;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\TipoNomenclador")
     */
    private $tiponom;

    /**
     * @ORM\ManyToMany(targetEntity="BMN\ReferenciaBundle\Entity\Referencia", mappedBy="fuentesInfo")
     */
    private $referencias;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $activo;


    /**
     *
     */
    public function __construct()
    {
        $this->referencias = new ArrayCollection();
    }

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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Nomenclador
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set tiponom
     *
     * @param $tiponom
     * @return Nomenclador
     */
    public function setTiponom($tiponom)
    {
        $this->tiponom = $tiponom;

        return $this;
    }

    /**
     * Get tiponom
     *
     * @return integer
     */
    public function getTiponom()
    {
        return $this->tiponom;
    }

    /**
     * @return mixed
     */
    public function getReferencias()
    {
        return $this->referencias;
    }

    /**
     * @return boolean
     */
    public function isActivo()
    {
        return $this->activo;
    }

    /**
     * @param $activo
     * @return Nomenclador
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getDescripcion();
    }
}
