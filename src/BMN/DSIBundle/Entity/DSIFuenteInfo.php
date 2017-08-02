<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\DSIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BMN\ReferenciaBundle\Entity\RefeFuenteInfo
 *
 * @ORM\Table(name="refe_fuenteInfo")
 */
class DSIFuenteInfo
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BMN\DSIBundle\Entity\DSI")
     */
    private $dsi_id;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $nomenclador_id;

}