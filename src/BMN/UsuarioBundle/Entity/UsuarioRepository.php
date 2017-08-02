<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace BMN\UsuarioBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\AST\JoinAssociationPathExpression;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;


class UsuarioRepository extends EntityRepository
{
    public function findUsuariosFiltros($filtros)
    {
        $where = '';
        if (!is_null($filtros['nombres'])) {
            $where = $where . ' AND u.nombres LIKE :nombres ';
        }
        if (!is_null($filtros['apellidos'])) {
            $where = $where . ' AND u.apellidos LIKE :apellidos ';
        }
        if (!is_null($filtros['pais'])) {
            $where = $where . ' AND u.pais = :pais ';
        }
        if (!is_null($filtros['email'])) {
            $where = $where . ' AND u.email LIKE :email ';
        }
        if (!is_null($filtros['telefono'])) {
            $where = $where . ' AND u.telefono LIKE :telefono ';
        }
        if (!is_null($filtros['tipoPro'])) {
            $where = $where . ' AND u.tipoPro = :tipoPro ';
        }
        if (!is_null($filtros['especialidad'])) {
            $where = $where . ' AND u.especialidad = :especialidad ';
        }
        if (!is_null($filtros['profesion'])) {
            $where = $where . ' AND u.profesion = :profesion ';
        }
        if (!is_null($filtros['categOcup'])) {
            $where = $where . ' AND u.categOcup = :categOcup ';
        }
        if (!is_null($filtros['categCien'])) {
            $where = $where . ' AND u.categCien = :categCien ';
        }
        if (!is_null($filtros['categDoc'])) {
            $where = $where . ' AND u.categDoc = :categDoc ';
        }
        if (!is_null($filtros['categInv'])) {
            $where = $where . ' AND u.categInv = :categInv ';
        }
        if (!is_null($filtros['cargo'])) {
            $where = $where . ' AND u.cargo = :cargo ';
        }
        if (!is_null($filtros['institucion'])) {
            $where = $where . ' AND u.institucion = :institucion ';
        }
        if (!is_null($filtros['dedicacion'])) {
            $where = $where . ' AND u.dedicacion = :dedicacion ';
        }
        if (!is_null($filtros['experiencia'])) {
            $where = $where . ' AND u.experiencia = :experiencia ';
        }
        if (!is_null($filtros['tipoUsua'])) {
            $where = $where . ' AND u.tipoUsua = :tipoUsua ';
        }
        if (!is_null($filtros['fechaInsDesde'])) {
            $where = $where . ' AND u.fechaIns >= :fechaInsDesde ';
        }
        if (!is_null($filtros['fechaInsHasta'])) {
            $where = $where . ' AND u.fechaIns <= :fechaInsHasta ';
        }
        if (!is_null($filtros['carnetBib'])) {
            $where = $where . ' AND u.carnetBib LIKE :carnetBib ';
        }
        if (!is_null($filtros['carnetId'])) {
            $where = $where . ' AND u.carnetId LIKE :carnetId ';
        }
        if (!is_null($filtros['atendidoPor'])) {
            $where = $where . ' AND u.atendidoPor = :atendidoPor ';
        }
        if (!is_null($filtros['estudiante']) && $filtros['estudiante'] == true) {
            $where = $where . ' AND u.estudiante = :estudiante ';
        }
        if (!is_null($filtros['inside']) && $filtros['inside'] == '1') {
            $where = $where . ' AND EXISTS (SELECT r FROM RecepcionBundle:Recepcion r WHERE u.id = r.usuario AND r.salida IS NULL) ';
        }
        $orderBy = '';
        $from = '';
        if (!is_null($filtros['orden']) && (!is_null($filtros['direccion'])) && ($filtros['orden'] != 'institucion')) {
            $orderBy = ' u.' . $filtros['orden'] . ' ' . $filtros['direccion'] . ', ';
        } elseif ($filtros['orden'] == 'institucion') {
            $from = ' LEFT JOIN u.institucion i ';
            $orderBy = ' i.descripcion ' . $filtros['direccion'] . ', ';
        }

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT u FROM UsuarioBundle:Usuario u ' . $from . ' WHERE 1 = 1' . $where . ' ORDER BY ' . $orderBy . ' u.fechaIns DESC, u.carnetBib DESC'
        );

        if (!is_null($filtros['nombres'])) {
            $consulta->setParameter('nombres', '%' . $filtros['nombres'] . '%', 'string');
        }
        if (!is_null($filtros['apellidos'])) {
            $consulta->setParameter('apellidos', '%' . $filtros['apellidos'] . '%', 'string');
        }
        if (!is_null($filtros['pais'])) {
            $consulta->setParameter('pais', $filtros['pais'], 'integer');
        }
        if (!is_null($filtros['email'])) {
            $consulta->setParameter('email', '%' . $filtros['email'] . '%', 'string');
        }
        if (!is_null($filtros['telefono'])) {
            $consulta->setParameter('telefono', '%' . $filtros['telefono'] . '%', 'integer');
        }
        if (!is_null($filtros['tipoPro'])) {
            $consulta->setParameter('tipoPro', $filtros['tipoPro'], 'integer');
        }
        if (!is_null($filtros['especialidad'])) {
            $consulta->setParameter('especialidad', $filtros['especialidad'], 'integer');
        }
        if (!is_null($filtros['profesion'])) {
            $consulta->setParameter('profesion', $filtros['profesion'], 'integer');
        }
        if (!is_null($filtros['categOcup'])) {
            $consulta->setParameter('categOcup', $filtros['categOcup'], 'integer');
        }
        if (!is_null($filtros['categCien'])) {
            $consulta->setParameter('categCien', $filtros['categCien'], 'integer');
        }
        if (!is_null($filtros['categDoc'])) {
            $consulta->setParameter('categDoc', $filtros['categDoc'], 'integer');
        }
        if (!is_null($filtros['categInv'])) {
            $consulta->setParameter('categInv', $filtros['categInv'], 'integer');
        }
        if (!is_null($filtros['cargo'])) {
            $consulta->setParameter('cargo', $filtros['cargo'], 'integer');
        }
        if (!is_null($filtros['institucion'])) {
            $consulta->setParameter('institucion', $filtros['institucion'], 'integer');
        }
        if (!is_null($filtros['dedicacion'])) {
            $consulta->setParameter('dedicacion', $filtros['dedicacion'], 'integer');
        }
        if (!is_null($filtros['experiencia'])) {
            $consulta->setParameter('experiencia', $filtros['experiencia'], 'integer');
        }
        if (!is_null($filtros['tipoUsua'])) {
            $consulta->setParameter('tipoUsua', $filtros['tipoUsua'], 'integer');
        }
        if (!is_null($filtros['fechaInsDesde'])) {
            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
            $fechaInsDesde = explode('/', $filtros['fechaInsDesde']);
            $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
            $consulta->setParameter('fechaInsDesde', $fechaDesde, 'date');
        }
        if (!is_null($filtros['fechaInsHasta'])) {
            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
            $fechaInsHasta = explode('/', $filtros['fechaInsHasta']);
            $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0]);
            $consulta->setParameter('fechaInsHasta', $fechaHasta, 'date');
        }
        if (!is_null($filtros['carnetBib'])) {
            $consulta->setParameter('carnetBib', '%' . $filtros['carnetBib'] . '%', 'integer');
        }
        if (!is_null($filtros['carnetId'])) {
            $consulta->setParameter('carnetId', '%' . $filtros['carnetId'] . '%', 'integer');
        }
        if (!is_null($filtros['atendidoPor'])) {
            $consulta->setParameter('atendidoPor', $filtros['atendidoPor'], 'integer');
        }
        if (!is_null($filtros['estudiante']) && $filtros['estudiante'] == true) {
            $consulta->setParameter('estudiante', $filtros['estudiante'], 'integer');
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
        $tableObjectName = 'UsuarioBundle:Usuario';
        /**
         * Set to default
         */
        if (!isset($get['columns']) || empty($get['columns'])) {
            $get['columns'] = array('id');
        }
        $aColumns = array();
        $aliases = array();
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->distinct(true);
        $aliasCounter = 0;
        foreach ($get['columns'] as $value) {
            switch ($value) {
                case 'especialidad':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS especialidad';
                    $aliases['especialidad'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['especialidad'],
                        'WITH',
                        'u.especialidad = ' . $aliases['especialidad'] . '.id'
                    );
                    break;
                case 'profesion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS profesion';
                    $aliases['profesion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['profesion'],
                        'WITH',
                        'u.profesion = ' . $aliases['profesion'] . '.id'
                    );
                    break;
                case 'dedicacion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS dedicacion';
                    $aliases['dedicacion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['dedicacion'],
                        'WITH',
                        'u.dedicacion = ' . $aliases['dedicacion'] . '.id'
                    );
                    break;
                case 'institucion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS institucion';
                    $aliases['institucion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['institucion'],
                        'WITH',
                        'u.institucion = ' . $aliases['institucion'] . '.id'
                    );
                    break;
                case 'tipoUsua':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS tipoUsua';
                    $aliases['tipoUsua'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['tipoUsua'],
                        'WITH',
                        'u.tipoUsua = ' . $aliases['tipoUsua'] . '.id'
                    );
                    break;
                case 'tipoPro':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS tipoPro';
                    $aliases['tipoPro'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['tipoPro'],
                        'WITH',
                        'u.tipoPro = ' . $aliases['tipoPro'] . '.id'
                    );
                    break;
                case 'categOcup':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categOcup';
                    $aliases['categOcup'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categOcup'],
                        'WITH',
                        'u.categOcup = ' . $aliases['categOcup'] . '.id'
                    );
                    break;
                case 'categCien':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categCien';
                    $aliases['categCien'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categCien'],
                        'WITH',
                        'u.categCien = ' . $aliases['categCien'] . '.id'
                    );
                    break;
                case 'categInv':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categInv';
                    $aliases['categInv'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categInv'],
                        'WITH',
                        'u.categInv = ' . $aliases['categInv'] . '.id'
                    );
                    break;
                case 'categDoc':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categDoc';
                    $aliases['categDoc'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categDoc'],
                        'WITH',
                        'u.categDoc = ' . $aliases['categDoc'] . '.id'
                    );
                    break;
                case 'pais':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS pais';
                    $aliases['pais'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['pais'],
                        'WITH',
                        'u.pais = ' . $aliases['pais'] . '.id'
                    );
                    break;
                case 'cargo':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS cargo';
                    $aliases['cargo'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['cargo'],
                        'WITH',
                        'u.cargo = ' . $aliases['cargo'] . '.id'
                    );
                    break;
                case 'atendidoPor':
                    $aColumns[] = 'n' . $aliasCounter . '.nombre AS atendidoPor, n' . $aliasCounter . '.apellidos AS atendidoPorApellidos';
                    $aliases['atendidoPor'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'AppUserBundle:AppUSer',
                        $aliases['atendidoPor'],
                        'WITH',
                        'u.atendidoPor = ' . $aliases['atendidoPor'] . '.id'
                    );
                    break;
                default:
                    if (!is_null($value) and $value != '') {
                        $aColumns[] = $alias . '.' . $value;
                    }
            }
        }
        $cb->select(str_replace(" , ", " ", implode(", ", $aColumns)));

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
                    $cb->orderBy($aliases['institucion'] . '.descripcion', $dir);
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
         *
         */
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
                        case "carnetId":
                        case "telefono":
                        case "experiencia":
                            $aLike[] = $cb->expr()->like('u.' . $clave, '\'%' . $valor . '%\'');
                            break;
                        case 'inactivo':
                            if ($valor == 1) {
                                $aLike[] = $cb->expr()->in('u.activo', '0,1');
                            } else {
                                $aLike[] = $cb->expr()->in('u.activo', '1');
                            }
                            break;
                        case "estudiante":
                            if ($valor)
                                $aLike[] = $cb->expr()->eq('u.' . $clave, $valor);
                            break;
                        case "inside":
                            if ($valor) {
                                $cb->add(
                                    'from',
                                    'RecepcionBundle:Recepcion r' . strval($i),
                                    true
                                )->andWhere('u.id = r' . strval($i) . '.usuario');
                                $aLike[] = $cb->expr()->isNull('r' . strval($i) . '.salida');
                            }
                            break;
                        case "autoservicio":
                            $aLike[] = $cb->expr()->in($aliases['tipoUsua'] . '.id', "531, 532");
                            break;
                        case "currentlyInNav":
                            if ($valor) {
                                $cb->add(
                                    'from',
                                    'NavegacionBundle:Navegacion g' . strval($i),
                                    true
                                )
                                    ->andWhere('u.id = g' . strval($i) . '.usuario');
                                $aLike[] = $cb->expr()->isNull('g' . strval($i) . '.salida');
                            }
                            break;
                        case "currentlyInLect":
                            if ($valor) {
                                $cb->add(
                                    'from',
                                    'LecturaBundle:Lectura l' . strval($i),
                                    true
                                )
                                    ->andWhere('u.id = l' . strval($i) . '.usuario');
                                $aLike[] = $cb->expr()->isNull('l' . strval($i) . '.salida');
                            }
                            break;
                        case "fechaInsDesde":
                            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsDesde = explode('/', $valor);
                            $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                            $aLike[] = $cb->expr()->gte('u.fechaIns', ':fechaInsDesde');
                            $cb->setParameter('fechaInsDesde', $fechaDesde, 'date');
                            break;
                        case "fechaInsHasta":
                            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsHasta = explode('/', $valor);
                            $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                            $aLike[] = $cb->expr()->lte('u.fechaIns', ':fechaInsHasta');
                            $cb->setParameter('fechaInsHasta', $fechaHasta, 'date');
                            break;
                        case "pais":
                        case "tipoPro":
                        case "profesion":
                        case "cargo":
                        case "dedicacion":
                        case "especialidad":
                        case "categCien":
                        case "categDoc":
                        case "categInv":
                        case "categOcup":
                        case "tipoUsua":
                            $aLike[] = $cb->expr()->eq($aliases[$clave] . '.id', $valor->getId());
                            break;
                        default:
                            if (!array_key_exists($clave, $aliases)) {
                                break;
                            }
                            $aLike[] = $cb->expr()->eq($aliases[$clave] . '.id', $valor);
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
            ->createQuery('SELECT COUNT(a) FROM UsuarioBundle:Usuario a')
            ->setMaxResults(1)
            ->getResult();

        return $aResultTotal[0][1];
    }


    /**
     * @param array $get
     * @param array $filters
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     */
    public function ajaxHistoricTable(array $get, array $filters, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'u';
        /* DB table to use */
        $tableObjectName = 'UsuarioBundle:Usuario';
        /**
         * Set to default
         */
        if (!isset($get['columns']) || empty($get['columns'])) {
            $get['columns'] = array('id');
        }
        $aColumns = array();
        $aliases = array();
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->distinct(true);
        $aliasCounter = 0;
        foreach ($get['columns'] as $value) {
            switch ($value) {
                case 'especialidad':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS especialidad';
                    $aliases['especialidad'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['especialidad'],
                        'WITH',
                        'u.especialidad = ' . $aliases['especialidad'] . '.id'
                    );
                    break;
                case 'profesion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS profesion';
                    $aliases['profesion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['profesion'],
                        'WITH',
                        'u.profesion = ' . $aliases['profesion'] . '.id'
                    );
                    break;
                case 'dedicacion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS dedicacion';
                    $aliases['dedicacion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['dedicacion'],
                        'WITH',
                        'u.dedicacion = ' . $aliases['dedicacion'] . '.id'
                    );
                    break;
                case 'institucion':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS institucion';
                    $aliases['institucion'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['institucion'],
                        'WITH',
                        'u.institucion = ' . $aliases['institucion'] . '.id'
                    );
                    break;
                case 'tipoUsua':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS tipoUsua';
                    $aliases['tipoUsua'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['tipoUsua'],
                        'WITH',
                        'u.tipoUsua = ' . $aliases['tipoUsua'] . '.id'
                    );
                    break;
                case 'tipoPro':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS tipoPro';
                    $aliases['tipoPro'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['tipoPro'],
                        'WITH',
                        'u.tipoPro = ' . $aliases['tipoPro'] . '.id'
                    );
                    break;
                case 'categOcup':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categOcup';
                    $aliases['categOcup'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categOcup'],
                        'WITH',
                        'u.categOcup = ' . $aliases['categOcup'] . '.id'
                    );
                    break;
                case 'categCien':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categCien';
                    $aliases['categCien'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categCien'],
                        'WITH',
                        'u.categCien = ' . $aliases['categCien'] . '.id'
                    );
                    break;
                case 'categInv':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categInv';
                    $aliases['categInv'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categInv'],
                        'WITH',
                        'u.categInv = ' . $aliases['categInv'] . '.id'
                    );
                    break;
                case 'categDoc':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS categDoc';
                    $aliases['categDoc'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['categDoc'],
                        'WITH',
                        'u.categDoc = ' . $aliases['categDoc'] . '.id'
                    );
                    break;
                case 'pais':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS pais';
                    $aliases['pais'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['pais'],
                        'WITH',
                        'u.pais = ' . $aliases['pais'] . '.id'
                    );
                    break;
                case 'cargo':
                    $aColumns[] = 'n' . $aliasCounter . '.descripcion AS cargo';
                    $aliases['cargo'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'NomencladorBundle:Nomenclador',
                        $aliases['cargo'],
                        'WITH',
                        'u.cargo = ' . $aliases['cargo'] . '.id'
                    );
                    break;
                case 'atendidoPor':
                    $aColumns[] = 'n' . $aliasCounter . '.nombre AS atendidoPor, n' . $aliasCounter . '.apellidos AS atendidoPorApellidos';
                    $aliases['atendidoPor'] = 'n' . $aliasCounter;
                    $aliasCounter = $aliasCounter + 1;
                    $cb->leftJoin(
                        'AppUserBundle:AppUser',
                        $aliases['atendidoPor'],
                        'WITH',
                        'u.atendidoPor = ' . $aliases['atendidoPor'] . '.id'
                    );
                    break;
                default:
                    $aColumns[] = $alias . '.' . $value;
            }
        }
        $cb->select(str_replace(" , ", " ", implode(", ", $aColumns)));

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
                    $cb->orderBy($aliases['institucion'] . '.descripcion', $dir);
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
                        case "carnetId":
                        case "telefono":
                        case "experiencia":
                            $aLike[] = $cb->expr()->like('u.' . $clave, '\'%' . $valor . '%\'');
                            break;
                        case "estudiante":
                            if ($valor) {
                                $aLike[] = $cb->expr()->eq('u.' . $clave, $valor);
                            }
                            break;
                        case "inside":
                            $cb->add(
                                'from',
                                'RecepcionBundle:Recepcion r' . strval($i),
                                true
                            )
                                ->andWhere('u.id = r' . strval($i) . '.usuario');
                            $aLike[] = $cb->expr()->isNull('r' . strval($i) . '.salida');
                            break;

                        case "currentlyInNav":
                            $cb->add(
                                'from',
                                'NavegacionBundle:Navegacion g' . strval($i),
                                true
                            )
                                ->andWhere('u.id = g' . strval($i) . '.usuario');
                            $aLike[] = $cb->expr()->isNull('g' . strval($i) . '.salida');
                            break;
//                        case "atendidoPor":
//                            $cb->add(
//                                'from',
//                                'AppUserBundle:AppUser a' . strval($i),
//                                true
//                            )
//                                ->andWhere('u.' . $clave . ' = a' . strval($i) . '.id');
//                            $aLike[] = $cb->expr()->eq('a' . strval($i) . '.id', $valor->getId());
//                            break;
                        case "fechaInsDesde":
                            $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsDesde = explode('/', $valor);
                            $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                            $aLike[] = $cb->expr()->gte('u.fechaIns', ':fechaInsDesde');
                            $cb->setParameter('fechaInsDesde', $fechaDesde, 'date');
                            break;
                        case "fechaInsHasta":
                            $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
                            $fechaInsHasta = explode('/', $valor);
                            $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);
                            $aLike[] = $cb->expr()->lte('u.fechaIns', ':fechaInsHasta');
                            $cb->setParameter('fechaInsHasta', $fechaHasta, 'date');
                            break;
                        default:
                            $aLike[] = $cb->expr()->eq($aliases[$clave] . '.id', $valor);
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
    public function getHistoricCount()
    {
        $aResultTotal = $this->getEntityManager()
            ->createQuery('SELECT COUNT(a) FROM UsuarioBundle:Usuario a')
            ->setMaxResults(1)
            ->getResult();

        return $aResultTotal[0][1];
    }

}
