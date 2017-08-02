<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\AppUserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Orx;


class AppUserRepository extends EntityRepository
{
    /**
     * @param array $get
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     * @internal param array $filters
     */
    public function ajaxTable(array $get, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'u';
        /* DB table to use */
        $tableObjectName = 'AppUserBundle:Appuser';
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('r')
            ->leftJoin('u.roles', 'r')
            ->addGroupBy('u.id');

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
                    case 'roles':
                        $aLike[] = $cb->expr()->like('r.name', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    default:
                        if ($colName != 'id') {
                            $aLike[] = $cb->expr()->like('u.' . $colName, '\'%' . $get['search']['value'] . '%\'');
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
        if (isset($get['order'])) {
            for ($i = 0; $i < intval($get['order']); $i++) {
                $dir = $get['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                if ($get['columns'][intval($get['order'][$i]['column'])] == 'roles') {
                    $cb->orderBy('r.name', $dir);
                } else {
                    $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);
                }

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
        $query = $this->getEntityManager()
            ->getRepository('AppUserBundle:AppUser')
            ->createQueryBuilder('u')
            ->addSelect('r')
            ->leftJoin('u.roles', 'r');

        return count($query->getQuery()->getArrayResult());
    }

}
