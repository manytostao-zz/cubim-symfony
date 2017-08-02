<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 14/02/2015
 * Time: 8:43
 */

namespace BMN;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CUBiMController
 * @package BMN
 */
class CUBiMController extends Controller
{

    /**
     * Gets app's notifications.
     *
     * @return array Notifications
     */
    public function getNotifications()
    {
        $em = $this->getDoctrine()->getManager();
        $roles = $this->get('security.context')->getToken()->getUser()->getRoles();
        $result = array();
        $query = null;
        $noti1 = 0;
        $noti2 = 0;
        foreach ($roles as $role) {
            switch ($role->getRole()) {
                case "ROLE_SUPER_ADMINISTRACIÓN":
                    if ($noti1 == 0) {
                        $noti1 = $noti1 + 1;

                        $query = $em->createQuery(
                            'SELECT u.nombres, u.id
                         FROM RecepcionBundle:Recepcion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today'
                        );
                        $query->setParameter('today', new \DateTime(), 'date');
                        array_push($result, $query->getResult());
                    }
                    if ($noti2 == 0) {
                        $noti2 = $noti2 + 1;
                        $query = $em->createQuery(
                            "SELECT COUNT(r)
                         FROM ReferenciaBundle:Referencia r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                        );
                        $results = $query->getResult();
                        if ($results[0][1] > 0) {
                            array_push($result, $query->getResult());
                        }
                    }
                    break;
                case "ROLE_RECEPCION":
                    if ($noti1 == 0) {
                        $noti1 = $noti1 + 1;

                        $query = $em->createQuery(
                            'SELECT u.nombres, u.id
                         FROM RecepcionBundle:Recepcion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today'
                        );
                        $query->setParameter('today', new \DateTime(), 'date');
                        array_push($result, $query->getResult());
                    }
                    break;
                case "ROLE_REFERENCIA":
                    if ($noti2 == 0) {
                        $noti2 = $noti2 + 1;
                        $query = $em->createQuery(
                            "SELECT COUNT(r)
                         FROM ReferenciaBundle:Referencia r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                        );
                        $results = $query->getResult();
                        if ($results[0][1] > 0) {
                            array_push($result, $query->getResult());
                        }
                    }
                    break;
            }
        }


        return $result;
    }

    /**
     * Gets app's nomenclators.
     *
     * @return array Nomenclators
     */
    public function getNomenclators()
    {
        $em = $this->getDoctrine()->getManager();
        $roles = $this->get('security.context')->getToken()->getUser()->getRoles();
        $query = null;
        $noti1 = 0;
        foreach ($roles as $role) {
            switch ($role->getRole()) {
                case "ROLE_SUPER_ADMINISTRACIÓN" :
                    if ($noti1 == 0) {
                        $noti1 = $noti1 + 1;
                        $query = $em->createQuery(
                            "SELECT n FROM NomencladorBundle:TipoNomenclador n ORDER BY n.descripcion"
                        );
                    }
                    break;
                case "ROLE_ADMINISTRACIÓN" :
                    if ($noti1 == 0) {
                        $noti1 = $noti1 + 1;
                        $query = $em->createQuery(
                            "SELECT n FROM NomencladorBundle:TipoNomenclador n ORDER BY n.descripcion"
                        );
                    }
                    break;
            }
        }

        return $query != null ? $query->getResult() : $query;
    }

    /**
     * @param $objects
     * @return array
     */
    protected function getChoicesArray($objects)
    {
        $choices = array();
        for ($i = 0; $i < count($objects); ++$i) {
            $choices[$objects[$i]->getId()] = $objects[$i];
        }

        return $choices;
    }

    // Parses a string into a DateTime object, optionally forced into the given timezone.
    /**
     * @param $string
     * @param null $timezone
     * @return \DateTime
     */
    protected function parseDateTime($string, $timezone = null)
    {
        $date = new \DateTime(
            $string,
            $timezone ? $timezone : new \DateTimeZone('UTC')
        // Used only when the string is ambiguous.
        // Ignored if string has a timezone offset in it.
        );
        if ($timezone) {
            // If our timezone was ignored above, force it.
            $date->setTimezone($timezone);
        }

        return $date;
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function usersListAction(Request $request, $filtros, $columnas)
    {
        $get = $request->request->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('UsuarioBundle:Usuario')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('UsuarioBundle:Usuario')->getCount());
        $query = $em->getRepository('UsuarioBundle:Usuario')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == 'fechaIns') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] == 'atendidoPor') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]] . ' ' . $aRow['atendidoPorApellidos'];
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getUsersHistoric(Request $request, $filtros, $columnas)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('UsuarioBundle:Usuario')->ajaxHistoricTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('UsuarioBundle:Usuario')->getCount());
        $query = $em->getRepository('UsuarioBundle:Usuario')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "pc") {
                    /* Special output formatting for 'version' column */
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "usuario") {
                    /* Special output formatting for 'version' column */
                    $row[] = $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'];
                } elseif ($columns[$i] == "correo") {
                    /* Special output formatting for 'version' column */
                    $row[] = (is_null($aRow[$columns[$i]]) or $aRow[$columns[$i]] == false) ? 'No' : 'Sí';
                } elseif ($columns[$i] == "fuentesInfo") {
                    $fuentes = '';
                    foreach ($aRow[$columns[$i]] as $fuente) {
                        if ($fuentes != '') {
                            $fuentes = $fuentes . ', ' . $fuente['descripcion'];
                        } else {
                            $fuentes = $fuente['descripcion'];
                        }
                    }
                    $row[] = $fuentes;
                } elseif ($columns[$i] == 'fecha') {

                    $date = getdate($aRow['entrada']->getTimestamp());
                    if ($date['mday'] < 10) {
                        if ($date['mon'] < 10) {
                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                        } else {
                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                        }
                    } else {
                        if ($date['mon'] < 10) {
                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                        } else {
                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                        }
                    }

                } elseif ($columns[$i] == 'entrada' or $columns[$i] == 'salida') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
//                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($date['mon'] < 10) {
//                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @return Response
     */
    public function trazasListAction(Request $request, $filtros)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('modulo', 'operacion', 'objeto', 'appUser', 'fecha', 'observaciones');
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('OtrosBundle:Traza')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('OtrosBundle:Traza')->getCount());
        $query = $em->getRepository('OtrosBundle:Traza')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == 'fecha') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($date['mon'] < 10) {
//                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $tipoNom
     * @return Response
     */
    public function nomencladorListAction(Request $request, $tipoNom)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('id', 'descripcion', 'activo');
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('NomencladorBundle:Nomenclador')->ajaxTable($get, $tipoNom, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladorBundle:Nomenclador')->getCount($tipoNom));
        $query = $em->getRepository('NomencladorBundle:Nomenclador')->ajaxTable($get, $tipoNom, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $columnas
     * @return Response
     */
    public function appUsersList(Request $request, $columnas)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('AppUserBundle:AppUser')->ajaxTable($get, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('AppUserBundle:AppUser')->getCount());
        $query = $em->getRepository('AppUserBundle:AppUser')->ajaxTable($get, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == 'roles') {
                    $appUserRoles = $em->getRepository('AppUserBundle:AppUser')->find($aRow['id']);
                    $roles = '';
                    foreach ($appUserRoles->getRoles() as $rol) {
                        if ($roles != '') {
                            $roles = $roles . ', ' . $rol->getName();
                        } else {
                            $roles = $rol->getName();
                        }
                    }
                    $row[] = $roles;
                } else {
                    if ($columns[$i] != ' ') {
                        /* General output */
                        $row[] = $aRow[$columns[$i]];
                    }
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getRecepcionEntradas(Request $request, $filtros, $columnas)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('RecepcionBundle:Recepcion')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('RecepcionBundle:Recepcion')->getCount(
                array_key_exists('usuario', $filtros) ? $filtros['usuario'] : null
            )
        );
        $query = $em->getRepository('RecepcionBundle:Recepcion')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "chapilla") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == 'fecha') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] == 'entrada' or $columns[$i] == 'salida') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
//                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($date['mon'] < 10) {
//                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getNavegacionEntradas(Request $request, $filtros, $columnas)
    {
        $get = $request->query->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('NavegacionBundle:Navegacion')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('NavegacionBundle:Navegacion')->getCount(
                array_key_exists('usuario_id', $filtros) ? $filtros['usuario_id'] : null
            )
        );
        $query = $em->getRepository('NavegacionBundle:Navegacion')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $nav = $em->getRepository('NavegacionBundle:Navegacion')->find($aRow['id']);
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "pc") {
                    /* Special output formatting for 'version' column */
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "usuario") {
                    /* Special output formatting for 'version' column */
                    $row[] = $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'];
                } elseif ($columns[$i] == "correo") {
                    /* Special output formatting for 'version' column */
                    $row[] = (is_null($aRow[$columns[$i]]) or $aRow[$columns[$i]] == false) ? 'No' : 'Sí';
                } elseif ($columns[$i] == "fuentesInfo") {
                    $fuentes = '';
                    foreach ($nav->getFuentesInfo() as $fuente) {
                        if ($fuentes != '') {
                            $fuentes = $fuentes . ', ' . $fuente->getDescripcion();
                        } else {
                            $fuentes = $fuente->getDescripcion();
                        }
                    }
                    $row[] = $fuentes;
                } elseif ($columns[$i] == 'fecha') {

                    $date = getdate($aRow['entrada']->getTimestamp());
                    if ($date['mday'] < 10) {
                        if ($date['mon'] < 10) {
                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                        } else {
                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                        }
                    } else {
                        if ($date['mon'] < 10) {
                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                        } else {
                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                        }
                    }

                } elseif ($columns[$i] == 'entrada' or $columns[$i] == 'salida') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
//                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($date['mon'] < 10) {
//                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
//                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getPreguntas(Request $request, $filtros, $columnas)
    {
        $get = $request->request->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('ReferenciaBundle:Referencia')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('ReferenciaBundle:Referencia')->getCount(
                array_key_exists('usuario', $filtros) ? $filtros['usuario'] : null
            )
        );
        $query = $em->getRepository('ReferenciaBundle:Referencia')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $pregunta = $em->getRepository('ReferenciaBundle:Referencia')->find($aRow['id']);
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "usuario" and !is_null($aRow['usuario']['id'])) {
                    $row[] = '<a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $aRow['usuario']['id'], 'modulo' => 'referencia', 'page' => 1)
                        ) . '">' . $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'] . '</a>';
                } elseif ($columns[$i] == "atendidoPor") {
                    $row[] = $aRow['appUser']['nombre'] . ' ' . $aRow['appUser']['apellidos'];
                } elseif ($columns[$i] == "viaSolicitud") {
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "viaSolicitud") {
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "adjunto") {
                    $solicitud = $em->getRepository('ReferenciaBundle:Referencia')->find($aRow['id']);
                    $row[] = '<a href="' . $solicitud->getWebPath() . '">' . $aRow['path'] . '</a>';
                } elseif ($columns[$i] == "desiderata") {
                    $row[] = $aRow['desiderata'] == 1 ? 'Sí' : 'No';
                } elseif ($columns[$i] == "fuentesInfo") {
                    $fuentes = '';
                    foreach ($pregunta->getFuentesInfo() as $fuente) {
                        if ($fuentes != '') {
                            $fuentes = $fuentes . ', ' . $fuente->getDescripcion();
                        } else {
                            $fuentes = $fuente->getDescripcion();
                        }
                    }
                    $row[] = $fuentes;
                } elseif ($columns[$i] == "tipo") {
                    $tipos = '';
                    if ($aRow['documento'] != '') {
                        $tipos = 'Documento';
                    }
                    if ($aRow['referencia']) {
                        if ($tipos != '') {
                            $tipos = $tipos . ', Referencia';
                        } else {
                            $tipos = 'Referencia';
                        }
                    }
                    if ($aRow['verbal']) {
                        if ($tipos != '') {
                            $tipos = $tipos . ', Respuesta';
                        } else {
                            $tipos = 'Respuesta';
                        }
                    }
                    $row[] = $tipos;
                } elseif ($columns[$i] == 'fechaSolicitud') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] == 'fechaRespuesta') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getPreguntasDSI(Request $request, $filtros, $columnas)
    {
        $get = $request->request->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('DSIBundle:DSI')->ajaxTable($get, $filtros, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('DSIBundle:DSI')->getCount(
                array_key_exists('usuario', $filtros) ? $filtros['usuario'] : null
            )
        );
        $query = $em->getRepository('DSIBundle:DSI')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $pregunta = $em->getRepository('DSIBundle:DSI')->find($aRow['id']);
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "usuario" and !is_null($aRow['usuario']['id'])) {
                    $row[] = '<a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $aRow['usuario']['id'], 'modulo' => 'dsi', 'page' => 1)
                        ) . '">' . $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'] . '</a>';
                } elseif ($columns[$i] == "atendidoPor") {
                    $row[] = $aRow['appUser']['nombre'] . ' ' . $aRow['appUser']['apellidos'];
                } elseif ($columns[$i] == "viaSolicitud") {
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "viaSolicitud") {
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "adjunto") {
                    $solicitud = $em->getRepository('DSIBundle:DSI')->find($aRow['id']);
                    $row[] = '<a href="' . $solicitud->getWebPath() . '">' . $aRow['path'] . '</a>';
                } elseif ($columns[$i] == "desiderata") {
                    $row[] = $aRow['desiderata'] == 1 ? 'Sí' : 'No';
                } elseif ($columns[$i] == "fuentesInfo") {
                    $fuentes = '';
                    foreach ($pregunta->getFuentesInfo() as $fuente) {
                        if ($fuentes != '') {
                            $fuentes = $fuentes . ', ' . $fuente->getDescripcion();
                        } else {
                            $fuentes = $fuente->getDescripcion();
                        }
                    }
                    $row[] = $fuentes;
                } elseif ($columns[$i] == "tipo") {
                    $tipos = '';
                    if ($aRow['documento'] != '') {
                        $tipos = 'Documento';
                    }
                    if ($aRow['referencia']) {
                        if ($tipos != '') {
                            $tipos = $tipos . ', Referencia';
                        } else {
                            $tipos = 'Referencia';
                        }
                    }
                    if ($aRow['verbal']) {
                        if ($tipos != '') {
                            $tipos = $tipos . ', Respuesta';
                        } else {
                            $tipos = 'Respuesta';
                        }
                    }
                    $row[] = $tipos;
                } elseif ($columns[$i] == 'fechaSolicitud') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] == 'fechaRespuesta') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getBibSolicitudes(Request $request, $filtros, $columnas)
    {
        $get = $request->request->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('BibliografiaBundle:Bibliografia')->ajaxTable(
            $get,
            $filtros,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('BibliografiaBundle:Bibliografia')->getCount(
                array_key_exists('referencia', $filtros) ? $filtros['referencia'] : false,
                array_key_exists('dsi', $filtros) ? $filtros['dsi'] : false,
                array_key_exists('usuario', $filtros) ? $filtros['usuario'] : null
            )
        );
        $query = $em->getRepository('BibliografiaBundle:Bibliografia')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            $manyToManyR = $em->getRepository('BibliografiaBundle:BibliografiaNomenclador')->findBy(
                array('bibliografia' => $aRow['id'])
            );
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "usuario" and !is_null($aRow['usuario']['id'])) {
                    $row[] = '<a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $aRow['usuario']['id'], 'modulo' => 'bibliografia', 'page' => 1)
                        ) . '">' . $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'] . '</a>';
                } elseif ($columns[$i] == "appUser") {
                    if ($aRow['appUser'] != "") {
                        $row[] = $aRow['appUser']['nombre'] . ' ' . $aRow['appUser']['apellidos'];
                    } else {
                        $row[] = "Autoservicio";
                    }
                } elseif ($columns[$i] == "motivo" or $columns[$i] == "estilo") {
                    $row[] = $aRow[$columns[$i]]['descripcion'];
                } elseif ($columns[$i] == "annos") {
                    $row[] = $aRow['fechaDesde'] . '-' . $aRow['fechaHasta'];
                } elseif ($columns[$i] == "idiomas") {
                    $idiomas = '';
                    foreach ($manyToManyR as $idioma) {
                        if ($idioma->getNomenclador()->getTipoNom()->getDescripcion() == 'Idiomas') {
                            if ($idiomas != '') {
                                $idiomas = $idiomas . ', ' . $idioma->getNomenclador()->getDescripcion();
                            } else {
                                $idiomas = $idioma->getNomenclador()->getDescripcion();
                            }
                        }
                    }
                    $row[] = $idiomas;
                } elseif ($columns[$i] == "tiposDocs") {
                    $tiposDocs = '';
                    foreach ($manyToManyR as $tipoDoc) {
                        if ($tipoDoc->getNomenclador()->getTipoNom()->getDescripcion() == 'Tipos de Documentos') {
                            if ($tiposDocs != '') {
                                $tiposDocs = $tiposDocs . ', ' . $tipoDoc->getNomenclador()->getDescripcion();
                            } else {
                                $tiposDocs = $tipoDoc->getNomenclador()->getDescripcion();
                            }
                        }
                    }
                    $row[] = $tiposDocs;
                } elseif ($columns[$i] == 'fechaSolicitud' or $columns[$i] == 'fechaRespuesta') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }

    /**
     * @param Request $request
     * @param $filtros
     * @param $columnas
     * @return Response
     */
    public function getLecturaEntradas(Request $request, $filtros, $columnas)
    {
        $get = $request->request->all();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = $columnas;
        $get['columns'] = &$columns;
        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('LecturaBundle:Lectura')->ajaxTable(
            $get,
            $filtros,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval(
            $em->getRepository('LecturaBundle:Lectura')->getCount(
                array_key_exists('usuario', $filtros) ? $filtros['usuario'] : null
            )
        );
        $query = $em->getRepository('LecturaBundle:Lectura')->ajaxTable($get, $filtros, true);
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            $lecturaModalidades = $em->getRepository('LecturaBundle:LecturaModalidad')->findBy(
                array('lectura' => $aRow['id'])
            );
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i] == "version") {
                    /* Special output formatting for 'version' column */
                    $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                } elseif ($columns[$i] == "usuario" and !is_null($aRow['usuario']['id'])) {
                    $row[] = '<a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $aRow['usuario']['id'], 'modulo' => 'lectura', 'page' => 1)
                        ) . '">' . $aRow[$columns[$i]]['nombres'] . ' ' . $aRow[$columns[$i]]['apellidos'] . '</a>';
                } elseif ($columns[$i] == "modalidades") {
                    $lms = '';
                    foreach ($lecturaModalidades as $lecturaModalidad) {
                        if ($lms != '') {
                            $lms = $lms . ', ' . $lecturaModalidad->getModalidad()->getDescripcion();
                        } else {
                            $lms = $lecturaModalidad->getModalidad()->getDescripcion();
                        }
                    }
                    $row[] = $lms;
                } elseif ($columns[$i] == 'entrada' or $columns[$i] == 'salida') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($aRow[$columns[$i]])) {
                        $row[] = '';
                    } else {
                        $date = getdate($aRow[$columns[$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            } else {
                                if ($date['hours'] < 10) {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                } else {
                                    if ($date['minutes'] < 10) {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    } else {
                                        if ($date['seconds'] < 10) {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                                        } else {
                                            $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($columns[$i] != ' ') {
                    /* General output */
                    $row[] = $aRow[$columns[$i]];
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }
}