<?php

namespace BMN\OtrosBundle\Controller;

use BMN\CUBiMController;
use BMN\OtrosBundle\Entity\Event;
use BMN\OtrosBundle\Entity\Traza;
use BMN\OtrosBundle\Form\EventType;
use BMN\OtrosBundle\Form\TrazaType;
use BMN\UsuarioBundle\Entity\Usuario;
use Doctrine\ORM\Query\Expr\Andx;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package BMN\OtrosBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $classActive = array('sup' => 'Panel Resumen', 'sub' => 'Ver');
        $em = $this->getDoctrine()->getManager();

        $temporales = $em->createQuery(
            "SELECT COUNT(n) FROM UsuarioBundle:Usuario n JOIN n.tipoUsua t WHERE t.descripcion = 'Temporal'"
        );
        $temporales = $temporales->getSingleResult();

        $potenciales = $em->createQuery(
            "SELECT COUNT(n) FROM UsuarioBundle:Usuario n JOIN n.tipoUsua t WHERE t.descripcion = 'Potencial'"
        );
        $potenciales = $potenciales->getSingleResult();

        $masters = $em->createQuery(
            "SELECT COUNT(n) FROM UsuarioBundle:Usuario n JOIN n.categCien t WHERE t.descripcion = 'Máster'"
        );
        $masters = $masters->getSingleResult();

        $doctores = $em->createQuery(
            "SELECT COUNT(n) FROM UsuarioBundle:Usuario n JOIN n.categCien t WHERE t.descripcion = 'Doctor en Ciencias' OR t.descripcion = 'Doctor en Ciencias Médicas'"
        );
        $doctores = $doctores->getSingleResult();

        return $this->render(
            "OtrosBundle:Default:index.html.twig",
            array(
                'active' => $classActive,
                'temporales' => $temporales,
                'potenciales' => $potenciales,
                'masters' => $masters,
                'doctores' => $doctores,
            )
        );
    }

    /**
     * @return Response
     */
    public function dataAction()
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $em = $this->getDoctrine()->getManager();
        $fechas = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);

        $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
        $fechaInsDesde = explode('/', $fechas->desde);
        $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);

        $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
        $fechaInsHasta = explode('/', $fechas->hasta);
        $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[1], $fechaInsHasta[0] + 1);

        $consulta = $em->createQuery(
            'SELECT COUNT(n) FROM UsuarioBundle:UsuarioServicio n WHERE n.servicio = :servicio
              AND n.fecha BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');

        $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findBy(array('tiponom' => 13));
        $recepcion = null;
        $cursos = 0;
        foreach ($servicios as $servicio) {
            switch ($servicio->getDescripcion()) {
                case 'Referencia':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $recepcion['referencia'] = $consulta->getSingleResult();
                    break;
                case 'Bibliografía':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $recepcion['bibliografia'] = $consulta->getSingleResult();
                    break;
                case 'Sala de Lectura':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $recepcion['sala_de_lectura'] = $consulta->getSingleResult();
                    break;
                case 'Sala de Navegación':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $recepcion['sala_de_navegacion'] = $consulta->getSingleResult();
                    break;
                case 'DSI':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $recepcion['dsi'] = $consulta->getSingleResult();
                    break;
                case 'Cursos':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $cursos = $consulta->getSingleResult();
                    break;
                case 'Conferencias':
                    $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                    $conferencias = $consulta->getSingleResult();
                    break;
            }
        }

        $consulta = $em->createQuery(
            'SELECT COUNT(r) FROM ReferenciaBundle:Referencia r WHERE r.fechaSolicitud BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $referencia = $consulta->getSingleResult();
        $consulta = $em->createQuery(
            'SELECT COUNT(r) FROM BibliografiaBundle:BibliografiaRespuesta r JOIN r.bibliografia b
            WHERE r.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta AND b.referencia = 1'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $result = $consulta->getSingleResult();
        $referencia[1] = $referencia[1] + $result[1];

        $consulta = $em->createQuery(
            'SELECT COUNT(r) FROM DSIBundle:DSI r WHERE r.fechaSolicitud BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $dsi = $consulta->getSingleResult();
        $consulta = $em->createQuery(
            'SELECT COUNT(r) FROM BibliografiaBundle:BibliografiaRespuesta r JOIN r.bibliografia b
            WHERE r.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta AND b.dsi = 1'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $result = $consulta->getSingleResult();
        $dsi[1] = $dsi[1] + $result[1];

        $consulta = $em->createQuery(
            'SELECT COUNT(n) FROM NavegacionBundle:Navegacion n WHERE n.entrada BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $sala_de_navegacion = $consulta->getSingleResult();

        $consulta = $em->createQuery(
            'SELECT COUNT(n) FROM LecturaBundle:Lectura n WHERE n.entrada BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $sala_de_lectura = $consulta->getSingleResult();

        $consulta = $em->createQuery(
            'SELECT COUNT(n) FROM BibliografiaBundle:BibliografiaRespuesta n WHERE n.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $bibliografia = $consulta->getSingleResult();

        $consulta = $em->createQuery(
            'SELECT n FROM RecepcionBundle:Recepcion n WHERE  n.entrada BETWEEN :fechaDesde AND :fechaHasta'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $resultados = $consulta->getResult();

        $potenciales = 0;
        $temporales = 0;
        $unclassified = 0;
        foreach ($resultados as $resultado) {
            switch ($resultado->getUsuario()->getTipoUsua()) {
                case 'Temporal':
                    $temporales = $temporales + 1;
                    break;

                case 'Potencial':
                    $potenciales = $potenciales + 1;
                    break;

                default:
                    $unclassified = $unclassified + 1;
            }
        }
        $total = $potenciales + $temporales + $unclassified;

        $consulta = $em->createQuery(
            'SELECT u.id, u.nombres, u.apellidos, COUNT(n) as visitas, MAX(n.entrada) AS ultima
            FROM RecepcionBundle:Recepcion n JOIN UsuarioBundle:Usuario u WITH n.usuario = u.id
            WHERE  n.entrada BETWEEN :fechaDesde AND :fechaHasta
            GROUP BY u.id, u.nombres, u.apellidos
            ORDER BY visitas DESC'
        );
        $consulta->setMaxResults(5);
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $usuariosMAS = $consulta->getResult();
        for ($i = 0; $i < count($usuariosMAS); $i = $i + 1) {
            $date = new \DateTime($usuariosMAS[$i]['ultima']);
            $usuariosMAS[$i]['ultima'] = date('d/m/Y H:i:s', $date->getTimestamp());

        }

        $consulta = $em->createQuery(
            'SELECT r
            FROM BibliografiaBundle:BibliografiaRespuesta r JOIN BibliografiaBundle:Bibliografia b WITH r.bibliografia = b.id
            WHERE  r.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta
              AND (b.dsi = FALSE OR b.dsi IS NULL) AND (b.referencia = FALSE OR b.referencia IS NULL)'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $results = $consulta->getResult();
        $totalCitas = 0;
        foreach ($results as $result) {
            $citas = explode("\n", $result->getCitas());
            foreach ($citas as $cita) {
                if ($cita != "" and trim($cita) != "") {
                    $totalCitas += 1;
                }
            }
        }

        $consulta = $em->getRepository('NavegacionBundle:Navegacion')
            ->createQueryBuilder('n')
            ->select('f.descripcion', 'COUNT(f.descripcion) cantidad')
            ->leftJoin('n.fuentesInfo', 'f')
            ->addGroupBy('f.descripcion')
            ->addOrderBy('cantidad', 'DESC')
            ->addOrderBy('f.descripcion');
        $aLike[] = $consulta->expr()->gte('n.entrada', ':fechaDesde');
        $aLike[] = $consulta->expr()->lte('n.entrada', ':fechaHasta');
        $consulta->andWhere(new Andx($aLike));
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $resultado = $consulta->getQuery()->getResult();
        $navegacionMAS[] = $resultado[0];

        $consulta = $em->getRepository('NavegacionBundle:Navegacion')
            ->createQueryBuilder('n')
            ->select('COUNT(n)')
            ->andWhere('n.correo = 1');
        $aLike[] = $consulta->expr()->gte('n.entrada', ':fechaDesde');
        $aLike[] = $consulta->expr()->lte('n.entrada', ':fechaHasta');
        $consulta->andWhere(new Andx($aLike));
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $resultado = $consulta->getQuery()->getSingleResult();
        $navegacionMAS[] = $resultado;

        $consulta = $em->createQuery(
            'SELECT n.descripcion, md.detalle, md.tipo, COUNT(md.detalle) cantidad
                  FROM LecturaBundle:Lectura l,
                       LecturaBundle:LecturaModalidad lm,
                       LecturaBundle:ModalidadDetalle md,
                       NomencladorBundle:Nomenclador n
                 WHERE     l.id = lm.lectura
                       AND lm.id = md.lecturaModalidad
                       AND lm.modalidad = n.id
                       AND l.entrada BETWEEN :fechaDesde AND :fechaHasta
                GROUP BY n.descripcion, md.detalle
                ORDER BY md.tipo, n.descripcion, cantidad DESC'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $resultado = $consulta->getResult();
        $lecturaMAS = $resultado;

        $consulta = $em->createQuery(
            'SELECT n.descripcion, COUNT(n.descripcion) cantidad
              FROM LecturaBundle:Lectura l
                   INNER JOIN LecturaBundle:LecturaModalidad lm WITH l.id = lm.lectura
                   INNER JOIN NomencladorBundle:Nomenclador n WITH lm.modalidad = n.id
            WHERE l.entrada BETWEEN :fechaDesde AND :fechaHasta
            GROUP BY n.descripcion
            ORDER BY n.descripcion, cantidad DESC'
        );
        $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
        $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
        $resultado = $consulta->getResult();

        foreach ($resultado as $registro) {
            if (($registro['descripcion'] != $lecturaMAS[count($lecturaMAS) - 1]['descripcion'])
                || ($registro['tipo'] != $lecturaMAS[count($lecturaMAS) - 1]['tipo'])
            ) {
                $lecturaMAS[] = $registro;
            }
        }

        return new Response(
            json_encode(
                array(
                    'recepcion' => $recepcion,
                    'referencia' => $referencia,
                    'sala_de_lectura' => $sala_de_lectura,
                    'sala_de_navegacion' => $sala_de_navegacion,
                    'bibliografia' => $bibliografia,
                    'dsi' => $dsi,
                    'cursos' => $cursos,
                    'conferencias' => $conferencias,
                    'potenciales' => $potenciales,
                    'temporales' => $temporales,
                    'total_users' => $total,
                    'unclassified' => $unclassified,
                    'usuariosMAS' => $usuariosMAS,
                    'lecturaMAS' => $lecturaMAS,
                    'navegacionMAS' => $navegacionMAS,
                    'totalCitas' => $totalCitas
                )
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToUserAction()
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $tipoUsua = $this->getRequest()->get('tipoUsua');
        $categCien = $this->getRequest()->get('categCien');
        $categInv = $this->getRequest()->get('categInv');
        $sesion->set(
            'usuarioFiltros',
            array(
                'nombres' => null,
                'apellidos' => null,
                'email' => null,
                'telefono' => null,
                'tipoPro' => null,
                'especialidad' => null,
                'profesion' => null,
                'categOcup' => null,
                'categCien' => $categCien,
                'categInv' => $categInv,
                'categDoc' => null,
                'cargo' => null,
                'institucion' => null,
                'dedicacion' => null,
                'experiencia' => null,
                'tipoUsua' => $tipoUsua,
                'carnetBib' => null,
                'carnetId' => null,
                'fechaIns' => null,
                'fechaInsDesde' => null,
                'fechaInsHasta' => null,
                'atendidoPor' => null,
            )
        );
        $peticion->setSession($sesion);

        return $this->redirect(
            $this->generateUrl(
                'usuario_lista',
                array('modulo' => $sesion->has('modulo') ? $sesion->get('modulo') : 'recepcion')
            )
        );
    }

    /**
     * @return Response
     */
    public function contact_usAction()
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }

        return $this->render(
            "OtrosBundle:Default:contact-us.html.twig",
            array('searchForm' => $this->getSearchForm()->createView())
        );
    }

    /**
     * @return Response
     */
    public function interactiveChartAction()
    {
        $fechaPura = new \DateTime('today', new \DateTimeZone('America/Havana'));
        $data = array();

        $fecha = getDate($fechaPura->getTimestamp());
        $anno = $fecha['year'];
        for ($i = 1; $i <= 12; $i++) {
            switch ($i) {
                case 1:
                case 3:
                case 5:
                case 7:
                case 8:
                case 10:
                case 12:
                    $diaHasta = 31;
                    break;
                case 2:
                    $diaHasta = 28;
                    break;
                default:
                    $diaHasta = 30;
                    break;
            }

            $fechaDesde = new \DateTime($anno . '-' . $i . '-01');
            $fechaHasta = new \DateTime($anno . '-' . $i . '-' . $diaHasta);

            #region RECEPCION
            $em = $this->getDoctrine()->getManager();
            $consulta = $em->createQuery(
                'SELECT COUNT(recepcion)
            FROM RecepcionBundle:Recepcion recepcion
            WHERE recepcion.entrada BETWEEN :desde AND :hasta'
            );

            $consulta->setParameter('desde', $fechaDesde);
            $consulta->setParameter('hasta', $fechaHasta);
            $result = $consulta->getResult();
            if ($result[0][1] > 0) {
                $data['recepcion'][] = [$i, $result[0][1]];
            }
            #endregion

            #region REFERENCIA
            $consulta = $em->createQuery(
                'SELECT COUNT(r) FROM ReferenciaBundle:Referencia r WHERE r.fechaSolicitud BETWEEN :fechaDesde AND :fechaHasta'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $referencia = $consulta->getSingleResult();
            $consulta = $em->createQuery(
                'SELECT COUNT(r) FROM BibliografiaBundle:BibliografiaRespuesta r JOIN r.bibliografia b
            WHERE r.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta AND b.referencia = 1'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $result = $consulta->getSingleResult();
            $referencia[1] = $referencia[1] + $result[1];
            if ($referencia[1] > 0) {
                $data['referencia'][] = [$i, $referencia[1]];
            }
            #endregion

            #region OTROS SERVICIOS
            $consulta = $em->createQuery(
                'SELECT COUNT(n) FROM UsuarioBundle:UsuarioServicio n WHERE n.servicio = :servicio
              AND n.fecha BETWEEN :fechaDesde AND :fechaHasta'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');

            $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findBy(array('tiponom' => 13));
            foreach ($servicios as $servicio) {
                switch ($servicio->getDescripcion()) {
                    case 'Sala de Lectura':
                        $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                        $sala_de_lectura = $consulta->getSingleResult();
                        if ($sala_de_lectura[1] > 0) {
                            $data['sala_de_lectura'][] = [$i, $sala_de_lectura[1]];
                        }
                        break;
                    case 'Cursos':
                        $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                        $cursos = $consulta->getSingleResult();
                        if ($cursos[1] > 0) {
                            $data['cursos'][] = [$i, $cursos[1]];
                        }
                        break;
                    case 'Conferencias':
                        $consulta->setParameter('servicio', $servicio->getId(), 'integer');
                        $conferencias = $consulta->getSingleResult();
                        if ($conferencias[1] > 0) {
                            $data['conferencias'][] = [$i, $conferencias[1]];
                        }
                        break;
                }
            }
            #endregion
            #region BIBLIOGRAFIA
            $consulta = $em->createQuery(
                'SELECT COUNT(n) FROM BibliografiaBundle:BibliografiaRespuesta n WHERE n.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $bibliografia = $consulta->getSingleResult();
            if ($bibliografia[1] > 0) {
                $data['bibliografia'][] = [$i, $bibliografia[1]];
            }
            #endregion

            #region SALA DE NAVEGACION
            $consulta = $em->createQuery(
                'SELECT COUNT(n) FROM NavegacionBundle:Navegacion n WHERE n.entrada BETWEEN :fechaDesde AND :fechaHasta'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $sala_de_navegacion = $consulta->getSingleResult();
            if ($sala_de_navegacion[1] > 0) {
                $data['sala_de_navegacion'][] = [$i, $sala_de_navegacion[1]];
            }
            #endregion

            #region DSI
            $consulta = $em->createQuery(
                'SELECT COUNT(r) FROM DSIBundle:DSI r WHERE r.fechaSolicitud BETWEEN :fechaDesde AND :fechaHasta'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $dsi = $consulta->getSingleResult();
            $consulta = $em->createQuery(
                'SELECT COUNT(r) FROM BibliografiaBundle:BibliografiaRespuesta r JOIN r.bibliografia b
            WHERE r.fechaRespuesta BETWEEN :fechaDesde AND :fechaHasta AND b.dsi = 1'
            );
            $consulta->setParameter('fechaDesde', $fechaDesde, 'date');
            $consulta->setParameter('fechaHasta', $fechaHasta, 'date');
            $result = $consulta->getSingleResult();
            $dsi[1] = $dsi[1] + $result[1];
            if ($dsi[1] > 0) {
                $data['dsi'][] = [$i, $dsi[1]];
            }
            #endregion
        };

        return new Response(json_encode($data));
    }

    /**
     * @return mixed
     */
    public function getSearchForm()
    {
        $usuario = new Usuario();
        $sesion = $this->getRequest()->getSession();

        $searchForm = $this->createFormBuilder(
            $usuario,
            array('attr' => array('name' => 'searchForm', 'id' => 'searchForm', 'class' => 'art-search'))
        )
            ->add('tipoForm', 'hidden', array('data' => 0, 'mapped' => false))
            ->add(
                'nombres',
                'text',
                array(
                    'required' => false,
                    'label' => 'Nombre(s)',
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 100%'),
                )
            )
            ->setAction(
                $this->generateUrl(
                    'usuario_lista',
                    array('modulo' => $sesion->has('modulo') ? $sesion->get('modulo') : 'recepcion')
                )
            )
            ->setMethod('POST')
            ->getForm();

        return $searchForm;
    }

    #region Trazas
    /**
     * @return Response
     */
    public function trazasAction()
    {
        $classActive = array('sup' => 'Trazas');
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $em = $this->getDoctrine()->getManager();

        $trazaType = new TrazaType();
        $trazaType->setAction($this->generateUrl('otros_trazas'));
        $traza = new Traza();
        $formFiltros = $this->createForm($trazaType, $traza);
        if ($peticion->isMethod('POST') && ($peticion->request->has('formTraza'))) {
            $formFiltros->handleRequest($peticion);
            $form = $peticion->get('formTraza');
            $sesion->set(
                'trazasFiltros',
                array(
                    'modulo' => $form['modulo'] != '' ? $form['modulo'] : null,
                    'operacion' => $form['operacion'] != '' ? $form['operacion'] : null,
                    'objeto' => $form['objeto'] != '' ? $form['objeto'] : null,
                    'appUser' => $form['appUser'] != '' ? $traza->getAppUser() : null,
                    'fechaOper' => $form['fechaOper'] != '' ? $form['fechaOper'] : null,
                    'fechaDesde' => $form['fechaDesde'] != '' ? $form['fechaDesde'] : null,
                    'fechaHasta' => $form['fechaHasta'] != '' ? $form['fechaHasta'] : null,
                )
            );

        }

        return $this->render(
            'OtrosBundle:Trazas:list.html.twig',
            array(
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'formFiltros' => $formFiltros->createView(),
                'active' => $classActive,
            )
        );
    }

    /**
     * @param $basico
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function limpiarFiltrosTrazasAction($basico)
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('trazasFiltros');

        $this->getRequest()->setSession($sesion);

        if ($basico == '1') {
            return $this->redirect($this->generateUrl('otros_trazas'));
        } else {
            return $this->redirect($this->generateUrl('busqueda_avanzada'));
        }
    }

    /**
     * @return Response
     */
    public function getTrazasListAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $filtros = $filtros = $sesion->get('trazasFiltros') != null ? $filtros = $sesion->get(
            'trazasFiltros'
        ) : array();
        if ($request->get('from') == 'profile') {
            $appUser = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')->find(
                $request->get('id')
            );
            $filtros['appUser'] = $appUser->getNombre() . ' ' . $appUser->getApellidos();
        }

        return $this->trazasListAction($request, $filtros);
    }
    #endregion

    #region Calendario
    /**
     * @param null $currentDate
     * @return Response
     */
    public function calendarioAction($currentDate = null)
    {
        $event = new Event();
        $eventType = new EventType();
        $eventType->setAction($this->generateUrl('otros_crear_evento'));
        $form = $this->createForm($eventType, $event);

//        $date = new \DateTime(!is_null($currentDate) ? $currentDate : 'today');
        return $this->render(
            'OtrosBundle:Calendario:calendario.html.twig',
            array(
                'form' => $form->createView(),
//                'date' => $date

            )
        );
    }

    public function calendarioExportAction()
    {
//        $pdf = $this->container->get("white_october.tcpdf")->create();
//        // set document information
//        $pdf->SetCreator('CUBiM');
//        $pdf->SetAuthor(
//            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
//                'security.context'
//            )->getToken()->getUser()->getApellidos()
//        );
//        $pdf->SetTitle('Calendario');
//        $pdf->SetSubject('Calendario');
//        $pdf->SetKeywords('Calendario, PDF');
//        $pdf->setPrintHeader(false);
//        $pdf->setPrintFooter(false);
//        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
//        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
//        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//        $pdf->setFontSubsetting(true);
//        $pdf->SetFont('dejavusans', '', 12, '', true);
//        $pdf->AddPage();
//        $pdf->setTextShadow(
//            array(
//                'enabled' => false,
//                'depth_w' => 0.2,
//                'depth_h' => 0.2,
//                'color' => array(196, 196, 196),
//                'opacity' => 1,
//                'blend_mode' => 'Normal',
//            )
//        );

        return $this->render('OtrosBundle:Calendario:calendario_export.html.twig');

//        $pdf->writeHTML($html->getContent());
//
//        return new Response(
//            $pdf->Output(),
//            200,
//            array(
//                'Content-Type' => 'application/pdf',
//                'Content-Disposition' => 'attachment; filename="respuesta.pdf"',
//            )
//        );
    }

    /**
     * @return Response
     */
    public function calCreateEventAction()
    {
        $peticion = $this->getRequest();
        $datos = $peticion->get('formEvent');
        $em = $this->getDoctrine()->getManager();
        if (is_null($datos['id']) or $datos['id'] == '') {
            $event = new Event();
        } else {
            $event = $em->getRepository('OtrosBundle:Event')->find($datos['id']);
        }
        $eventType = new EventType();
        $eventType->setAction($this->generateUrl('otros_crear_evento'));
        $form = $this->createForm($eventType, $event);
        $newStartDate = null;
        $newEndDate = null;
        $form->handleRequest($peticion);
        if (array_key_exists('start', $datos) and !is_null($datos['start']) and $datos['start'] != '') {
            $newStartDate = \DateTime::createFromFormat('d/m/Y H:i:s', $datos["start"]);
        }
        if (array_key_exists('end', $datos) and !is_null($datos['end']) and $datos['end'] != '') {
            $newEndDate = \DateTime::createFromFormat('d/m/Y H:i:s', $datos["end"]);
        }

        $event->setStart($newStartDate);
        $event->setEnd($newEndDate);
        $em->persist($event);
        $em->flush();

        return $this->calendarioAction(/*$newStartDate->format('d/m/Y H:i:s')*/);

    }

    /**
     * @param $id
     * @return Response
     */
    public function calDeleteEventAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('OtrosBundle:Event')->find($id);
        $em->remove($event);
        $em->flush();

        return $this->calendarioAction();
    }

    /**
     * @return Response
     */
    public function eventosAction()
    {
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        if (is_null($peticion->get('start')) || is_null($peticion->get('end'))) {
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Fecha de incio y/o fecha de fin incorrectas'
            );

            return $this->calendarioAction();
        }

//        $timezone = new \DateTimeZone('America/Havana');
        $range_start = $this->parseDateTime($peticion->get('start'));
//        $range_start->setTimezone($timezone);
        $range_end = $this->parseDateTime($peticion->get('end'));
//        $range_end->setTimezone($timezone);
//        if (!is_null($peticion->get('timezone'))) {
//            $timezone = new \DateTimeZone($peticion->get('timezone'));
//        }
        $eventos = $em->getRepository('OtrosBundle:Event')->findAll();
        $output_arrays = array();
        foreach ($eventos as $evento) {
            if ($evento->isWithinDayRange($range_start, $range_end)) {
                $output_arrays[] = $evento->toArray();
            }
        }

        return new Response(
            json_encode($output_arrays)
        );
    }
    #endregion
}
