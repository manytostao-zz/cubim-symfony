<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 27/10/14
 * Time: 18:55
 */

namespace BMN\BibliografiaBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * BMN\BibliografiaBundle\Entity\BibliografiaNomenclador
 *
 * @ORM\Entity(repositoryClass="BMN\BibliografiaBundle\Entity\BibliografiaRepository")
 * @ORM\Table(name="bibliografia_nomenclador")
 */
class BibliografiaNomenclador
{
    /**
     * @ORM\Id
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\BibliografiaBundle\Entity\Bibliografia")
     */
    private $bibliografia;

    /**
     * @ORM\Id
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="BMN\NomencladorBundle\Entity\Nomenclador")
     */
    private $nomenclador;

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
     * @return int
     */
    public function getNomenclador()
    {
        return $this->nomenclador;
    }

    /**
     * @param int $nomenclador
     */
    public function setNomenclador($nomenclador)
    {
        $this->nomenclador = $nomenclador;
    }

}