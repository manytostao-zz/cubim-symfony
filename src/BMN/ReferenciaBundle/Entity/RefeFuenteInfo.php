<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\ReferenciaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\ReferenciaBundle\Entity\RefeFuenteInfo
 *
 * @ORM\Table(name="refe_fuenteInfo")
 */
class RefeFuenteInfo
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BMN\ReferenciaBundle\Entity\Referencia")
     */
    private $referencia_id;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $nomenclador_id;

}