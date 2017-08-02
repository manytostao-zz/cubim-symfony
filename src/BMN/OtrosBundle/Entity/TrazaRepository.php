<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\OtrosBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class TrazaRepository extends EntityRepository
{
    public function findTrazasFiltros($filtros)
    {
        $where = '';
        $em = $this->getEntityManager();
        if (!is_null($filtros['operacion'])) {
            $where = $where . ' AND u.operacion LIKE :operacion ';
        }
        if (!is_null($filtros['objeto'])) {
            $where = $where . ' AND u.objeto LIKE :objeto ';
        }
        if (!is_null($filtros['appUser'])) {

            $where = $where . ' AND u.appUser LIKE :appUser ';
        }
        if (!is_null($filtros['fechaDesde'])) {
            $where = $where . ' AND u.fecha >= :fechaDesde ';
        }
        if (!is_null($filtros['fechaHasta'])) {
            $where = $where . ' AND u.fecha <= :fechaHasta ';
        }

        $consulta = $em->createQuery(
            'SELECT u FROM OtrosBundle:Traza u WHERE 1 = 1' . $where . 'ORDER BY u.fecha DESC'
        );

        if (!is_null($filtros['operacion'])) {
            $consulta->setParameter('operacion', '%' . $filtros['operacion'] . '%', 'string');
        }
        if (!is_null($filtros['objeto'])) {
            $consulta->setParameter('objeto', '%' . $filtros['objeto'] . '%', 'string');
        }
        if (!is_null($filtros['appUser'])) {
            $consulta->setParameter('appUser', '%' . $filtros['appUser'] . '%', 'string');
        }
        if (!is_null($filtros['fechaDesde'])) {
            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
            $fechaFiltDesde = explode('/', $filtros['fechaDesde']);
            $fechaDesde->setDate($fechaFiltDesde[2], $fechaFiltDesde[1], $fechaFiltDesde[0]);
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        }
        if (!is_null($filtros['fechaHasta'])) {
            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
            $fechaFiltHasta = explode('/', $filtros['fechaHasta']);
            $fechaHasta->setDate($fechaFiltHasta[2], $fechaFiltHasta[1], $fechaFiltHasta[0]);
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        }

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
        $alias = 'u';
        /* DB table to use */
        $tableObjectName = 'OtrosBundle:Traza';
        /**
         * Set to default
         */
        if (!isset($get['columns']) || empty($get['columns'])) {
            $get['columns'] = array('id');
        }
        $aColumns = array();
        foreach ($get['columns'] as $value) {
                $aColumns[] = $alias . '.' . $value;
        }
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->select(str_replace(" , ", " ", implode(", ", $aColumns)));

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
                if ($get['columns'][intval($get['order'][$i]['column'])] == 'institucion') {
                    $cb->orderBy('n.descripcion', $dir);
                } else {
                    $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);
                }

            }
        }
        /*
        * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited

        if (isset($get['sSearch']) && $get['sSearch'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($get['bSearchable_' . $i]) && $get['bSearchable_' . $i] == "true") {
                    $aLike[] = $cb->expr()->like($aColumns[$i], '\'%' . $get['sSearch'] . '%\'');
                }
            }
            if (count($aLike) > 0) {
                $cb->andWhere(new Orx($aLike));
            } else {
                unset($aLike);
            }
        } */
        /*Para cuando tengo un solo buscador*/
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                $colName = $aColumns[$i];
                if (strpos($colName, ' AS ') != null) {
                    $colName = substr($colName, 0, strpos($colName, ' AS '));
                }
                $aLike[] = $cb->expr()->like($colName, '\'%' . $get['search']['value'] . '%\'');
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
                        case "modulo":
                        case "operacion":
                        case "objeto":
                        case "appUser":
                            $aLike[] = $cb->expr()->like('u.' . $clave, '\'%' . $valor . '%\'');
                            break;
                        case "fechaDesde":
                            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsDesde = explode('/', $valor);
                            $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                            $aLike[] = $cb->expr()->gte('u.fecha', ':fechaInsDesde');
                            $cb->setParameter('fechaInsDesde', $fechaDesde, 'date');
                            break;
                        case "fechaHasta":
                            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsHasta = explode('/', $valor);
                            $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0]);
                            $aLike[] = $cb->expr()->lte('u.fecha', ':fechaInsHasta');
                            $cb->setParameter('fechaInsHasta', $fechaHasta, 'date');
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
            return $query->getResult();
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery('SELECT COUNT(a) FROM OtrosBundle:Traza a')
            ->setMaxResults(1)
            ->getResult();

        return $aResultTotal[0][1];
    }

}