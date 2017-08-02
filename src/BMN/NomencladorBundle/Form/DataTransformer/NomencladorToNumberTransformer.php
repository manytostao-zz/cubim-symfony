<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 3/17/2016
 * Time: 2:47 PM
 */

namespace BMN\NomencladorBundle\Form\DataTransformer;

use BMN\NomencladorBundle\Entity\Nomenclador;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NomencladorToNumberTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param mixed $nomenclador
     * @return string
     * @internal param Issue|null $issue
     */
    public function transform($nomenclador)
    {
        if (null === $nomenclador) {
            return '';
        }

        return $nomenclador->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param mixed $nomencladorId
     * @return Nomenclador|null
     * @internal param string $issueNumber
     */
    public function reverseTransform($nomencladorId)
    {
        // no issue number? It's optional, so that's ok
        if (!$nomencladorId) {
            return;
        }

        $nomenclador = $this->manager
            ->getRepository('NomencladorBundle:Nomenclador')
            // query for the issue with this id
            ->find($nomencladorId);

        if (null === $nomenclador) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'El nomenclador de id "%s" no existe!',
                $nomencladorId
            ));
        }

        return $nomenclador;
    }
}