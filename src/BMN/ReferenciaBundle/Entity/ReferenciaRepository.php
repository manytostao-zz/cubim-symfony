<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\ReferenciaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class ReferenciaRepository extends EntityRepository
{
    public function findFiltros($filtros)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQueryBuilder()
            ->distinct(true)
            ->addSelect('r', 'f')
            ->from('ReferenciaBundle:Referencia', 'r')
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.appUser', 'a')
            ->leftJoin('r.viaSolicitud', 'n')
            ->leftJoin('r.fuentesInfo', 'f');
        if (!is_null($filtros) and array_key_exists('unanswered', $filtros) and !is_null($filtros['unanswered'])) {
            $consulta->andWhere("r.respuesta = '' OR r.respuesta is null");
        }
        if (!is_null($filtros) and array_key_exists('desiderata', $filtros) and !is_null($filtros['desiderata'])) {
            $consulta->andWhere("r.desiderata = 1");
        }
        if (!is_null($filtros) and array_key_exists('document', $filtros) and !is_null($filtros['document'])) {
            $consulta->andWhere("r.documento = 1");
        }
        if (!is_null($filtros) and array_key_exists('reference', $filtros) and !is_null($filtros['reference'])) {
            $consulta->andWhere("r.referencia = 1");
        }
        if (!is_null($filtros) and array_key_exists('answer', $filtros) and !is_null($filtros['answer'])) {
            $consulta->andWhere("r.verbal = 1");
        }
        if (!is_null($filtros) and array_key_exists('search', $filtros) and !is_null($filtros['search'])) {
            $like = array();
            $like[] = $consulta->expr()->like('u.nombres', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('u.apellidos', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('a.nombre', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('a.apellidos', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('r.pregunta', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('r.fechaSolicitud', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('r.fechaRespuesta', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('n.descripcion', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('f.descripcion', '\'%' . $filtros['search'] . '%\'');
            $like[] = $consulta->expr()->like('r.name', '\'%' . $filtros['search'] . '%\'');
            $consulta->andWhere(new Orx($like));
        }
        if (!is_null($filtros) and array_key_exists('order', $filtros)
            and !is_null($filtros['order'])
            and $filtros['order'] != ""
            and array_key_exists('dir', $filtros)
            and !is_null($filtros['dir'])
            and $filtros['dir'] != ""
        ) {
            switch ($filtros['order']) {
                case 'usuario':
                    $consulta->orderBy('u.nombres', $filtros['dir']);
                    $consulta->addOrderBy('u.apellidos', $filtros['dir']);
                    break;
                case 'appUser':
                    $consulta->orderBy('a.nombre', $filtros['dir']);
                    $consulta->addOrderBy('a.apellidos', $filtros['dir']);
                    break;
                case 'viaSolicitud':
                    $consulta->orderBy('n.descripcion', $filtros['dir']);
                    break;
                case 'fuentesInfo':
                    $consulta->orderBy('f.descripcion', $filtros['dir']);
                    break;
                default:
                    $consulta->orderBy('r.' . $filtros['order'], $filtros['dir']);
                    break;
            }

        }

        return $consulta->getQuery()->getResult();

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
        $alias = 'r';
        /* DB table to use */
        $tableObjectName = 'ReferenciaBundle:Referencia';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('u', 'a', 'v', 'f')
            ->distinct(true)
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.appUser', 'a')
            ->leftJoin('r.viaSolicitud', 'v')
            ->leftJoin('r.fuentesInfo', 'f')
            ->addGroupBy('r.id');

        if (isset($get['start']) && $get['length'] != '-1') {
            $cb->setFirstResult((int)$get['start'])
                ->setMaxResults((int)$get['length']);
        }
        /*
        * Ordering
        */
        /*Para cuando tengo un solo buscador*/
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    case 'usuario':
                        $aLike[] = $cb->expr()->like('u.nombres', '\'%' . $get['search']['value'] . '%\'');
                        $aLike[] = $cb->expr()->like('u.apellidos', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'atendidoPor':
                        $aLike[] = $cb->expr()->like('a.nombre', '\'%' . $get['search']['value'] . '%\'');
                        $aLike[] = $cb->expr()->like('a.apellidos', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'viaSolicitud':
                        $aLike[] = $cb->expr()->like('v.descripcion', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'adjunto':
                        $aLike[] = $cb->expr()->like('r.name', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'fuentesInfo':
                        $aLike[] = $cb->expr()->like('f.descripcion', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'tipo':
                        if (substr_count('documento', $get['search']['value']) > 0) {
                            $aLike[] = $cb->expr()->eq('r.documento', 1);
                        }
                        if (substr_count('referencia', $get['search']['value']) > 0) {
                            $aLike[] = $cb->expr()->eq('r.referencia', 1);
                        }
                        if (substr_count('respuesta', $get['search']['value']) > 0) {
                            $aLike[] = $cb->expr()->eq('r.verbal', 1);
                        }
                        break;
                    default:
                        if ($colName != 'id') {
                            $aLike[] = $cb->expr()->like('r.' . $colName, '\'%' . $get['search']['value'] . '%\'');
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
                if ($get['columns'][intval($get['order'][$i]['column'])] == 'usuario') {
                    $cb->orderBy('u.nombres', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'atendidoPor') {
                    $cb->orderBy('a.nombre', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'viaSolicitud') {
                    $cb->orderBy('v.descripcion', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'adjunto') {
                    $cb->orderBy('r.name', $dir);
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
                        case 'usuario':
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->eq('u.id', $valor);
                            }
                            break;
                        case "unanswered":
                            if ($valor != '') {
                                $cb->andWhere('r.respuesta IS NULL');
                            }
                            break;
                        case "desiderata":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->eq('r.desiderata', 1);
                            }
                            break;
                        case "document":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->eq('r.documento', 1);
                            }
                            break;
                        case "answer":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->eq('r.verbal', 1);
                            }
                            break;
                        case "reference":
                            if ($valor != '') {
                                $aLike[] = $cb->expr()->eq('r.referencia', 1);
                            }
                            break;
                        case "fechaDesde":
                            if ($valor != '') {
                                $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsDesde = explode('/', $valor);
                                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                                $aLike[] = $cb->expr()->gte('r.fechaSolicitud', ':fechaDesde');
                                $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            }
                            break;
                        case "fechaHasta":
                            if ($valor != '') {
                                $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                                $fechaInsHasta = explode('/', $valor);
                                $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                                $aLike[] = $cb->expr()->lte('r.fechaSolicitud', ':fechaHasta');
                                $cb->setParameter('fechaHasta', $fechaHasta, 'date');
                            }
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
           ->getRepository('ReferenciaBundle:Referencia')
           ->createQueryBuilder('r')
           ->addSelect('u', 'a', 'v', 'f')
           ->distinct(true)
           ->leftJoin('r.usuario', 'u')
           ->leftJoin('r.appUser', 'a')
           ->leftJoin('r.viaSolicitud', 'v')
           ->leftJoin('r.fuentesInfo', 'f');
        if (!is_null($usuario)) {
            $query->andWhere('r.usuario = ' . $usuario);
        }

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}