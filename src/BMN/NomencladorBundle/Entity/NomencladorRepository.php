<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\NomencladorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Orx;


class NomencladorRepository extends EntityRepository
{
    public function findNomencladoresFiltros($filtros, $limit = null, $offset = null)
    {
        if (!empty($filtros['tiponom'])) {
            $where = ' AND n.tiponom = :tiponom ';
        } else {
            $where = ' AND n.tiponom = 1 ';
        }
        if (!empty($filtros['id'])) {
            $where = $where . ' AND n.id LIKE :id ';
        }
        if (!empty($filtros['descripcion'])) {
            $where = $where . ' AND n.descripcion LIKE :descripcion ';
        }
        if (!empty($filtros['activo'])) {
            $where = $where . ' AND n.activo LIKE :activo ';
        }

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT n FROM NomencladorBundle:Nomenclador n WHERE 1 = 1' . $where . 'ORDER BY n.descripcion ASC'
        );

        if(!is_null($limit))
            $consulta->setMaxResults($limit);
        if(!is_null($offset))
            $consulta->setFirstResult($offset);

        if (!empty($filtros['tiponom'])) {
            $consulta->setParameter('tiponom', $filtros['tiponom'], 'integer');
        }
        if (!empty($filtros['descripcion'])) {
            $consulta->setParameter('descripcion', '%' . $filtros['descripcion'] . '%', 'string');
        }
        if (!empty($filtros['id'])) {
            $consulta->setParameter('id', '%' . $filtros['id'] . '%', 'string');
        }
        if (!empty($filtros['activo'])) {
            $consulta->setParameter('activo', $filtros['activo'], 'boolean');
        }

        return $consulta->getResult();
    }


    /**
     * @param array $get
     * @param $tipoNom
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     * @internal param array $filters
     */
    public function ajaxTable(array $get, $tipoNom, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'n';
        /* DB table to use */
        $tableObjectName = 'NomencladorBundle:Nomenclador';
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->andWhere('n.tiponom = ' . $tipoNom);
        /**
         * Set to default
         */

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
                $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);

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
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    default:
                        if ($colName != 'id') {
                            $aLike[] = $cb->expr()->like('n.' . $colName, '\'%' . $get['search']['value'] . '%\'');
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
    public function getCount($tipoNom)
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery('SELECT COUNT(a) FROM NomencladorBundle:Nomenclador a WHERE a.tiponom = '.$tipoNom)
            ->setMaxResults(1)
            ->getResult();

        return $aResultTotal[0][1];
    }
} 