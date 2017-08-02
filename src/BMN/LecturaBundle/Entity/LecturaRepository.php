<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\LecturaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class LecturaRepository extends EntityRepository
{
    public function findCurrentlyInLect($id)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT u FROM LecturaBundle:Lectura u WHERE u.usuario=:id AND u.salida IS NULL'
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
        $alias = 'l';
        /* DB table to use */
        $tableObjectName = 'LecturaBundle:Lectura';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('u')
            ->distinct(true)
            ->leftJoin('l.usuario', 'u')
            ->addGroupBy('l.id');

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
                } else {
                    $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);
                }

            }
        }
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    case 'usuario':
                        $aLike[] = $cb->expr()->like('u.nombres', '\'%' . $get['search']['value'] . '%\'');
                        $aLike[] = $cb->expr()->like('u.apellidos', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    default:
                        if ($colName != 'id' and $colName != 'idiomas' and $colName != 'tiposDocs') {
                            $aLike[] = $cb->expr()->like('l.' . $colName, '\'%' . $get['search']['value'] . '%\'');
                        }
                        break;
                }
            }
            if (count($aLike) > 0) {
                $cb->andWhere(new Orx($aLike));
            } else {
                unset($aLike);
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
                                $aLike[] = $cb->expr()->eq('l.usuario', $valor);
                            }
                            break;
                        case 'modalidades':
                            $j = 0;
                            foreach ($valor as $modalidad) {
                                $aLike[] = $cb->expr()->exists(
                                    'SELECT lm' . $j . ' FROM LecturaBundle:LecturaModalidad lm' . $j
                                    . ' WHERE l.id = lm' . $j . '.lectura AND lm' . $j . '.modalidad = :modalidad' . $j . ''
                                );
                                $cb->setParameter('modalidad' . $j, $modalidad);
                                $j++;
                            }
                            break;
                        case 'detalle':
                            if ($valor != '')
                                $aLike[] = $cb->leftJoin('l.lecturaModalidad', 'lm')
                                    ->leftJoin('lm.modalidadDetalle', 'md')
                                    ->expr()->like('md.detalle', '\'%' . $valor . '%\'');
                            break;
                        case "fechaDesde":
                            if (!is_null($valor) and $valor != '') {
                                $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsDesde = explode('/', $valor);
                                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                                $aLike[] = $cb->expr()->gte('l.entrada', ':fechaDesde');
                                $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            }
                            break;
                        case "fechaHasta":
                            if (!is_null($valor) and $valor != '') {
                                $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsHasta = explode('/', $valor);
                                $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                                $aLike[] = $cb->expr()->lte('l.entrada', ':fechaHasta');
                                $cb->setParameter('fechaHasta', $fechaHasta, 'date');
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
                            $aLike[] = $cb->expr()->gte('l.entrada', ':fechaDesde');
                            $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            break;
                        case "fechaHasta":
                            $fechaHasta = new \DateTime("tomorrow", new \DateTimeZone('America/Havana'));
                            $aLike[] = $cb->expr()->lte('l.entrada', ':fechaHasta');
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
            ->getRepository('LecturaBundle:Lectura')
            ->createQueryBuilder('l')
            ->addSelect('u')
            ->distinct(true)
            ->leftJoin('l.usuario', 'u')
            ->addGroupBy('l.id');
        if (!is_null($usuario)) {
            $query->andWhere('l.usuario = ' . $usuario);
        }

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

    public function findDetallesByLectura($id)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT lm
            FROM LecturaBundle:LecturaModalidad lm
            WHERE lm.lectura=:id'
        );
        $consulta->setParameter('id', $id, 'integer');


        return $consulta->getResult();
    }

}