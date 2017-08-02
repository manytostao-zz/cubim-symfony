<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\UsuarioBundle\Entity;

use Doctrine\ORM\EntityRepository;


class UsuarioServicioRepository extends EntityRepository
{
    public function findChapilla($id)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT u FROM UsuarioBundle:UsuarioServicio u WHERE u.usuario=:id AND u.salida IS NULL'
        );
        $consulta->setParameter('id', $id, 'integer');


        return $consulta->getResult();
    }

}