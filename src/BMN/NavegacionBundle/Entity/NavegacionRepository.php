<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\NavegacionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class NavegacionRepository extends EntityRepository
{
    public function findCurrentlyInNav($id)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT u FROM NavegacionBundle:Navegacion u WHERE u.usuario=:id AND u.salida IS NULL'
        );
        $consulta->setParameter('id', $id, 'integer');


        return $consulta->getResult();
    }

    /**
     * @param array $get
     * @param array $filters
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     */
    public function ajaxTable(array $get, array $filters, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'n';
        /* DB table to use */
        $tableObjectName = 'NavegacionBundle:Navegacion';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('f', 'u', 'p')
            ->distinct(true)
            ->leftJoin('n.pc', 'p')
            ->leftJoin('n.usuario', 'u')
            ->leftJoin('n.fuentesInfo', 'f')
            ->addGroupBy('n.id');

        if (!isset($get['columns']) || empty($get['columns'])) {
            $get['columns'] = array('id');
        }

        if (isset($get['start']) && $get['length'] != '-1') {
            $cb->setFirstResult((int)$get['start'])
                ->setMaxResults((int)$get['length']);
        }
        /*
        * Ordering
        */

        if (isset($get['order'])) {
            for ($i = 0; $i < intval($get['order']); $i++) {
                $dir = $get['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                if ($get['columns'][intval($get['order'][$i]['column'])] == 'usuario') {
                    $cb->orderBy('u.nombres', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'pc') {
                    $cb->orderBy('p.descripcion', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'fuentesInfo') {
                    $cb->orderBy('f.descripcion', $dir);
                } else {
                    $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);
                }

            }
        }
        /*Resto de filtros*/
        if (!is_null($filters)) {
            $aLike = array();
            $i = 0;
            foreach ($filters as $clave => $valor) {
                if (!is_null($valor)) {
                    switch ($clave) {
                        case 'usuario_id':
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->eq('n.usuario', $valor);
                            }
                            break;
                        case "correo":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->like('n.' . $clave, '\'%' . $valor . '%\'');
                            }
                            break;
                        case "fechaDesde":
                            if (!is_null($valor) and $valor != '') {
                                $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsDesde = explode('/', $valor);
                                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                                $aLike[] = $cb->expr()->gte('n.entrada', ':fechaDesde');
                                $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            }
                            break;
                        case "fechaHasta":
                            if (!is_null($valor) and $valor != '') {
                                $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsHasta = explode('/', $valor);
                                $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                                $aLike[] = $cb->expr()->lte('n.entrada', ':fechaHasta');
                                $cb->setParameter('fechaHasta', $fechaHasta, 'date');
                            }
                            break;
                        case "pc":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->like('p.id', '\'%' . $valor . '%\'');
                            }
                            break;
                        case "fuentesInfo":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->like('f.id', '\'%' . $valor . '%\'');
                            }
                            break;
                        case "usuario":
                            if ($valor != '') {
                                $bLike[] = $cb->expr()->like('u.nombres', '\'%' . $valor . '%\'');
                                $bLike[] = $cb->expr()->like('u.apellidos', '\'%' . $valor . '%\'');
                                $cb->andWhere(new Orx($bLike));
                            }
                            break;
                    }

                } else {
                    switch ($clave) {
                        case "fechaDesde":
                            $fechaDesde = new \DateTime("today", new \DateTimeZone('America/Havana'));
                            $aLike[] = $cb->expr()->gte('n.entrada', ':fechaDesde');
                            $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            break;
                        case "fechaHasta":
                            $fechaHasta = new \DateTime("tomorrow", new \DateTimeZone('America/Havana'));
                            $aLike[] = $cb->expr()->lte('n.entrada', ':fechaHasta');
                            $cb->setParameter('fechaHasta', $fechaHasta, 'date');
                            break;
                    }
                }
                $i = $i + 1;
            }
            if (count($aLike) > 0) {
                $cb->andWhere(new Andx($aLike));
            } else {
                unset($aLike);
            }
        }

        /*
        * SQL queries
        * Get data to display
        */
        $query = $cb->getQuery();
        if ($flag) {
            return $query;
        } else {
            $results = $query->getResult();

            return $results;
        }
    }

    /**
     * @return int
     */
    public function getCount($usuario = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('NavegacionBundle:Navegacion')
            ->createQueryBuilder('n')
            ->addSelect('f', 'u', 'p')
            ->distinct(true)
            ->leftJoin('n.pc', 'p')
            ->leftJoin('n.usuario', 'u')
            ->leftJoin('n.fuentesInfo', 'f');
        if (!is_null($usuario)) {
            $query->andWhere('n.usuario = ' . $usuario);
        }

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}