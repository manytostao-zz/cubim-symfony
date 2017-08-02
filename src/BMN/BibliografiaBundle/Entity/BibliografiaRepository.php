<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\BibliografiaBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class BibliografiaRepository extends EntityRepository
{
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
        $tableObjectName = 'BibliografiaBundle:Bibliografia';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('u', 'a', 'm', 'e')
            ->distinct(true)
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.appUser', 'a')
            ->leftJoin('r.motivo', 'm')
            ->leftJoin('r.estilo', 'e');

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
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'appUser') {
                    $cb->orderBy('a.nombre', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'motivo') {
                    $cb->orderBy('m.descripcion', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'estilo') {
                    $cb->orderBy('e.descripcion', $dir);
                } elseif ($get['columns'][intval($get['order'][$i]['column'])] == 'annos') {
                    $cb->orderBy('r.fechaDesde', $dir);
                } else {
                    $cb->orderBy($alias.'.'.$get['columns'][intval($get['order'][$i]['column'])], $dir);
                }

            }
        }
        /*Para cuando tengo un solo buscador*/
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    case 'usuario':
                        $aLike[] = $cb->expr()->like('u.nombres', '\'%'.$get['search']['value'].'%\'');
                        $aLike[] = $cb->expr()->like('u.apellidos', '\'%'.$get['search']['value'].'%\'');
                        break;
                    case 'appUser':
                        $aLike[] = $cb->expr()->like('a.nombre', '\'%'.$get['search']['value'].'%\'');
                        $aLike[] = $cb->expr()->like('a.apellidos', '\'%'.$get['search']['value'].'%\'');
                        break;
                    case 'motivo':
                        $aLike[] = $cb->expr()->like('m.descripcion', '\'%'.$get['search']['value'].'%\'');
                        break;
                    case 'estilo':
                        $aLike[] = $cb->expr()->like('e.descripcion', '\'%'.$get['search']['value'].'%\'');
                        break;
                    case 'annos':
                        $aLike[] = $cb->expr()->like('r.fechaDesde', '\'%'.$get['search']['value'].'%\'');
                        $aLike[] = $cb->expr()->like('r.fechaHasta', '\'%'.$get['search']['value'].'%\'');
                        break;
                    default:
                        if ($colName != 'id' and $colName != 'idiomas' and $colName != 'tiposDocs') {
                            $aLike[] = $cb->expr()->like('r.'.$colName, '\'%'.$get['search']['value'].'%\'');
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
                        case 'usuario':
                            if (!is_null($valor) and $valor != '') {
                                $aLike[] = $cb->expr()->eq('r.usuario', $valor);
                            }
                            break;
                        case 'tipoDocs':
                            $j = 0;
                            foreach ($valor as $idioma) {
                                $aLike[] = $cb->expr()->exists(
                                    'SELECT bnt'.$j.' FROM BibliografiaBundle:BibliografiaNomenclador bnt'.$j
                                    .' WHERE r.id = bnt'.$j.'.bibliografia AND bnt'.$j.'.nomenclador = :tipoDocs'.$j.''
                                );
                                $cb->setParameter('tipoDocs'.$j, $idioma);
                                $j++;
                            }
                            break;
                        case 'idioma':
                            $j = 0;
                            foreach ($valor as $idioma) {
                                $aLike[] = $cb->expr()->exists(
                                    'SELECT bni'.$j.' FROM BibliografiaBundle:BibliografiaNomenclador bni'.$j
                                    .' WHERE r.id = bni'.$j.'.bibliografia AND bni'.$j.'.nomenclador = :idioma'.$j.''
                                );
                                $cb->setParameter('idioma'.$j, $idioma);
                                $j++;
                            }
                            break;
                        case "unanswered":
                            $cb2 = $this->getEntityManager()->createQueryBuilder();
                            $aLike[] = $cb->expr()->not(
                                $cb->expr()->exists(
                                    $cb2->add('select', 'br')
                                        ->add('from', 'BibliografiaBundle:BibliografiaRespuesta br')
                                        ->add('where', 'r.id = br.bibliografia')
                                )
                            );
                            break;
                        case "fechaDesde":
                            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsDesde = explode('/', $valor);
                            $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                            $aLike[] = $cb->expr()->gte('r.fechaSolicitud', ':fechaDesde');
                            $cb->setParameter('fechaDesde', $fechaDesde, 'date');
                            break;
                        case "fechaHasta":
                            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsHasta = explode('/', $valor);
                            $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                            $aLike[] = $cb->expr()->lte('r.fechaSolicitud', ':fechaHasta');
                            $cb->setParameter('fechaHasta', $fechaHasta, 'date');
                            break;
                        case "tema":
                            $aLike[] = $cb->expr()->like('r.'.$clave, '\'%'.$valor.'%\'');
                            break;
                        default:
                            if ($clave != "_token" && $clave != "filter_type") {
                                $aLike[] = $cb->expr()->eq('r.'.$clave, $valor);
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
            if(!array_key_exists('referencia', $filters)) {
                $aLike = array();
                $aLike[] = $cb->expr()->eq('r.referencia', 0);
                $aLike[] = $cb->expr()->isNull('r.referencia');
                $cb->andWhere(new Orx($aLike));
            }
            if(!array_key_exists('dsi', $filters)) {
                $aLike = array();
                $aLike[] = $cb->expr()->eq('r.dsi', 0);
                $aLike[] = $cb->expr()->isNull('r.dsi');
                $cb->andWhere(new Orx($aLike));
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
    public function getCount($referencia, $dsi, $usuario = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('BibliografiaBundle:Bibliografia')
            ->createQueryBuilder('r')
            ->addSelect('u', 'a', 'm', 'e')
            ->distinct(true)
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.appUser', 'a')
            ->leftJoin('r.motivo', 'm')
            ->leftJoin('r.estilo', 'e');



        if ($referencia) {
            $query->andWhere($query->expr()->eq('r.referencia', $referencia));
        }else{
            $aLike = array();
            $aLike[] = $query->expr()->eq('r.referencia', 0);
            $aLike[] = $query->expr()->isNull('r.referencia');
            $query->andWhere(new Orx($aLike));
        }

        if ($dsi) {
            $query->andWhere($query->expr()->eq('r.dsi', $dsi));
        }
        else{
        $aLike = array();
        $aLike[] = $query->expr()->eq('r.dsi', 0);
        $aLike[] = $query->expr()->isNull('r.dsi');
        $query->andWhere(new Orx($aLike));
    }

        if (!is_null($usuario)) {
            $query->andWhere('r.usuario = '.$usuario);
        }

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}