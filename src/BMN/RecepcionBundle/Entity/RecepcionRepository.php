<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\RecepcionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


/**
 * Class RecepcionRepository
 * @package BMN\RecepcionBundle\Entity
 */
class RecepcionRepository extends EntityRepository
{
    /**
     * @var
     */
    private $aColumns;

    /**
     * @param $id
     * @return array
     */
    public function findCurrentlyIn($id)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT u FROM RecepcionBundle:Recepcion u WHERE u.usuario=:id AND u.salida IS NULL'
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
        $tableObjectName = 'RecepcionBundle:Recepcion';
        /**
         * Set to default
         */
        if (!isset($get['columns']) || empty($get['columns'])) {
            $get['columns'] = array('id');
        }
        $aColumns = array();
        foreach ($get['columns'] as $value) {
            if ($value == 'fecha') {
                $aColumns[] = 'n.entrada as fecha';
            } elseif ($value == 'nombres') {
                $aColumns[] = 'u.nombres';
            } elseif ($value == 'apellidos') {
                $aColumns[] = 'u.apellidos';
            } elseif ($value == 'carnetId') {
                $aColumns[] = 'u.carnetId';
            } elseif ($value == 'servicio') {
                $aColumns[] = 'r.descripcion as servicio';
            }
//            elseif ($value == 'entrada') {
//                $aColumns[] = 'TIME(n.entrada) as entrada';
//            } elseif ($value == 'salida') {
//                $aColumns[] = 'TIME(n.salida) as salida';
//            }
            else {
                $aColumns[] = $alias . '.' . $value;
            }
        }
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->distinct(true)
            ->select(str_replace(" , ", " ", implode(", ", $aColumns)))
            ->innerJoin('UsuarioBundle:Usuario', 'u', 'WITH', 'u.id = n.usuario')
            ->innerJoin('UsuarioBundle:UsuarioServicio', 's', 'WITH', 'u.id = s.usuario AND s.fecha = n.entrada')
            ->innerJoin('NomencladorBundle:Nomenclador', 'r', 'WITH', 'r.id = s.servicio');

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
                if ($get['columns'][intval($get['order'][$i]['column'])] == 'nombres') {
                    $cb->orderBy('u.nombres', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'apellidos') {
                    $cb->orderBy('u.apellidos', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'carnetId') {
                    $cb->orderBy('u.carnetId', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'servicio') {
                    $cb->orderBy('r.descripcion', $dir);
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
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                if ($colName != 'fecha') {
                    if (strpos($colName, ' AS ') != null) {
                        $colName = substr($colName, 0, strpos($colName, ' AS '));
                    }
                    switch ($colName) {
                        case 'servicio':
                            $colName = 'r.descripcion';
                            break;
                        case 'chapilla':
                            $colName = 'n.chapilla';
                            break;
                        case 'observaciones':
                            $colName = 'n.observaciones';
                            break;
                        case 'entrada':
                            $colName = 'n.entrada';
                            break;
                        case 'salida':
                            $colName = 'n.salida';
                            break;
                    }
                    $aLike[] = $cb->expr()->like($colName, '\'%' . $get['search']['value'] . '%\'');
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
                        case 'usuario':
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->eq('n.usuario', $valor);
                            }
                            break;
                        case "nombres":
                        case "apellidos":
                        case "carnetId":
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->like('u.' . $clave, '\'%' . $valor . '%\'');
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
                        case "chapilla":
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->like('n.' . $clave, '\'%' . $valor . '%\'');
                            }
                            break;
                        case "servicio":
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->like('r.id' , '\'%' . $valor . '%\'');
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
        $this->aColumns = $aColumns;
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
            ->getRepository('RecepcionBundle:Recepcion')
            ->createQueryBuilder('n')
            ->distinct(true)
            ->select(str_replace(" , ", " ", implode(", ", $this->aColumns)))
            ->innerJoin('UsuarioBundle:Usuario', 'u', 'WITH', 'u.id = n.usuario')
            ->innerJoin('UsuarioBundle:UsuarioServicio', 's', 'WITH', 'u.id = s.usuario AND s.fecha = n.entrada')
            ->innerJoin('NomencladorBundle:Nomenclador', 'r', 'WITH', 'r.id = s.servicio');
        if (!is_null($usuario) and $usuario != '') {
            $query->andWhere('n.usuario = ' . $usuario);
        }

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }
}