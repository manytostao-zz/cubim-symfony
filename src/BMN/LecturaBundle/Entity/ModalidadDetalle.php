<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 12/10/15
 * Time: 13:54
 */

namespace BMN\LecturaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\LecturaBundle\Entity\ModalidadDetalle
 *
 * @ORM\Entity()
 * @ORM\Table(name="modalidad_detalle")
 * @Assert\Callback(methods={"validLibro"})
 */

class ModalidadDetalle
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
     * @ORM\ManyToOne(targetEntity="BMN\LecturaBundle\Entity\LecturaModalidad")
     */
    private $lecturaModalidad;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $detalle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $tipo;

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
    public function getLecturaModalidad()
    {
        return $this->lecturaModalidad;
    }

    /**
     * @param int $lecturaModalidad
     */
    public function setLecturaModalidad($lecturaModalidad)
    {
        $this->lecturaModalidad = $lecturaModalidad;
    }

    /**
     * @return string
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * @param string $detalle
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    }

    /**
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validLibro(ExecutionContextInterface $context)
    {
        if ($this->getTipo() == 'libro') {
            if (preg_match("/\d/", $this->getDetalle(), $out) !== 1) {
                $context->addViolationAt(
                    'detalle',
                    'El campo "Libro" debe ser un número.',
                    array(),
                    null
                );

                return;
            }
        }
    }

}