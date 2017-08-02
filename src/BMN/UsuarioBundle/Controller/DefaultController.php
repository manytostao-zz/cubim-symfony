<?php

namespace BMN\UsuarioBundle\Controller;

use BMN\BibliografiaBundle\Entity\Bibliografia;
use BMN\BibliografiaBundle\Form\BibliografiaType;
use BMN\CUBiMController;
use BMN\DSIBundle\Entity\DSI;
use BMN\DSIBundle\Form\DSIType;
use BMN\LecturaBundle\Entity\Lectura;
use BMN\LecturaBundle\Entity\LecturaModalidad;
use BMN\LecturaBundle\Entity\ModalidadDetalle;
use BMN\LecturaBundle\Form\LecturaModalidadType;
use BMN\LecturaBundle\Form\LecturaType;
use BMN\NavegacionBundle\Entity\Navegacion;
use BMN\NavegacionBundle\Form\NavegacionType;
use BMN\NomencladorBundle\Form\NomencladorType;
use BMN\OtrosBundle\Entity\Traza;
use BMN\ReferenciaBundle\Entity\Referencia;
use BMN\ReferenciaBundle\Form\ReferenciaType;
use BMN\UsuarioBundle\Entity\Usuario;
use BMN\UsuarioBundle\Entity\UsuarioServicio;
use BMN\UsuarioBundle\Form\UsuarioType;
use BMN\UsuarioBundle\Form\UsuarioServicioType;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DefaultController
 * @package BMN\UsuarioBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @param $modulo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function listadoAction($modulo)
    {
        //Capturando la petición y la sesión
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $sesion->set('modulo', $modulo);
        $classActive = array('sup' => $modulo, 'sub' => 'usuarios');

        /**********************************************************************
         * Aquí tuve que meterle el pie a los valores booleanos que venían
         * en la petición, porque llegan como string y el convertidor a booleano
         * de Symfony me ponía siempre true independientemente de lo que viniera
         * cuando le hacía handleRequest al formulario.
         **********************************************************************/
        $form = $peticion->request->has('form') ? $peticion->get('form') : array();
        foreach ($form as $key => $value) {
            switch ($key) {
                case 'estudiante':
                case 'inside':
                case 'inactivo':
                case 'currentlyInLect':
                case 'currentlyInNav':
                    $form[$key] = $form[$key] === 'true' ? true : false;
                    break;
            }
        }
        $peticion->request->set('form', $form);
        /**********************************************************************/

        $currentlyIn = $sesion->get('usuarioFiltros');

        $usuario = $this->getUsuarioFromSession($sesion);
        $formFiltros = $this->createForm(
            $this->getUsuarioType(
                0,
                false,
                $this->generateUrl('usuario_lista', array('modulo' => $sesion->get('modulo'))),
                $this->getDoctrine()->getManager(),
                $modulo,
                !is_null($currentlyIn) ? $currentlyIn['inside'] == "1" : (array_key_exists(
                    'inside',
                    is_array($form) ? $form : array()
                ) ? $form['inside'] == '1' : false),
                !is_null($currentlyIn) ? $currentlyIn['currentlyInNav'] == "1" : (array_key_exists(
                    'currentlyInNav',
                    is_array($form) ? $form : array()
                ) ? $form['currentlyInNav'] == '1' : false),
                !is_null($currentlyIn) ? $currentlyIn['currentlyInLect'] == "1" : (array_key_exists(
                    'currentlyInLect',
                    is_array($form) ? $form : array()
                ) ? $form['currentlyInLect'] == '1' : false),
                !is_null($currentlyIn) ? $currentlyIn['inactivo'] == "1" : (array_key_exists(
                    'inactivo',
                    is_array($form) ? $form : array()
                ) ? $form['inactivo'] == '1' : false),
                $usuario->getEstudiante(),
                false
            ),
            $usuario
        );
        if (($peticion->isMethod('POST') && ($peticion->request->has('form')))) {
            $formFiltros->handleRequest($peticion);
        }
        $sesion->set(
            'usuarioFiltros',
            array(
                'nombres' => $usuario->getNombres(),
                'apellidos' => $usuario->getApellidos(),
                'pais' => !is_null($usuario->getPais()) ? $usuario->getPais() : null,
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'tipoPro' => !is_null($usuario->getTipoPro()) ? $usuario->getTipoPro() : null,
                'especialidad' => !is_null($usuario->getEspecialidad()) ? $usuario->getEspecialidad() : null,
                'profesion' => !is_null($usuario->getProfesion()) ? $usuario->getProfesion() : null,
                'categOcup' => !is_null($usuario->getCategOcup()) ? $usuario->getCategOcup() : null,
                'categCien' => !is_null($usuario->getCategCien()) ? $usuario->getCategCien() : null,
                'categInv' => !is_null($usuario->getCategInv()) ? $usuario->getCategInv() : null,
                'categDoc' => !is_null($usuario->getCategDoc()) ? $usuario->getCategDoc() : null,
                'cargo' => !is_null($usuario->getCargo()) ? $usuario->getCargo() : null,
                'institucion' => !is_null($usuario->getInstitucion()) ? $usuario->getInstitucion()->getId() : null,
                'dedicacion' => !is_null($usuario->getDedicacion()) ? $usuario->getDedicacion() : null,
                'experiencia' => !is_null($usuario->getExperiencia()) ? $usuario->getExperiencia() : null,
                'tipoUsua' => !is_null($usuario->getTipoUsua()) ? $usuario->getTipoUsua() : null,
                'carnetBib' => !is_null($usuario->getCarnetBib()) ? $usuario->getCarnetBib() : null,
                'carnetId' => !is_null($usuario->getCarnetId()) ? $usuario->getCarnetId() : null,
                'fechaIns' => !is_null($usuario->getFechaIns()) ? $usuario->getFechaIns() : null,
                'fechaInsDesde' => array_key_exists(
                    'fechaInsDesde',
                    $form
                ) ? $form['fechaInsDesde'] != '' ? $form['fechaInsDesde'] : null : null,
                'fechaInsHasta' => array_key_exists(
                    'fechaInsHasta',
                    $form
                ) ? $form['fechaInsHasta'] != '' ? $form['fechaInsHasta'] : null : null,
                'fechaInsOper' => array_key_exists(
                    'fechaInsOper',
                    $form
                ) ? $form['fechaInsOper'] != '' ? $form['fechaInsOper'] : null : null,
                'atendidoPor' => !is_null($usuario->getAtendidoPor()) ? $usuario->getAtendidoPor()->getId() : null,
                'estudiante' => !is_null($usuario->getEstudiante()) ? $usuario->getEstudiante() : null,
                'inside' => !$peticion->isMethod('POST') ? $currentlyIn['inside'] : (array_key_exists(
                    'inside',
                    is_array($form) ? $form : array()
                ) ? $form['inside'] == "true" : null),
                'inactivo' => !$peticion->isMethod('POST') ? $currentlyIn['inactivo'] : (array_key_exists(
                    'inactivo',
                    is_array($form) ? $form : array()
                ) ? $form['inactivo'] == "true" : null),
                'currentlyInNav' => !$peticion->isMethod('POST') ? $currentlyIn['currentlyInNav'] : (array_key_exists(
                    'currentlyInNav',
                    is_array($form) ? $form : array()
                ) ? $form['currentlyInNav'] == "true" : null),
                'currentlyInLect' => !$peticion->isMethod('POST') ? $currentlyIn['currentlyInLect'] : (array_key_exists(
                    'currentlyInLect',
                    is_array($form) ? $form : array()
                ) ? $form['currentlyInLect'] == "true" : null),
                'orden' => $this->getRequest()->get('orden'),
                'direccion' => $this->getRequest()->get('direccion'),
            )
        );

        if ($modulo == "dsi") {
            $modulo = "DSI";
        }

        if (!isset($form['fromAjax'])) {
            return $this->render(
                ucfirst($modulo) . 'Bundle:Default:lista.html.twig',
                array(
                    'formFiltros' => $formFiltros->createView(),
                    'searchForm' => $this->getSearchForm()->createView(),
                    'active' => $classActive,
                    'itemsPerPage' => $sesion->get('itemsPerPage'),
                    'orden' => $this->getRequest()->get('orden'),
                    'direccion' => $this->getRequest()->get('direccion'),
                )
            );
        } else return new Response();
    }

    /**
     * @param $id
     * @param $modulo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function detallesAction(
        $id,
        $modulo
    )
    {
        $classActive = array('sup' => $modulo, 'sub' => 'usuarios');
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->find('UsuarioBundle:Usuario', $id);
        $form = null;
        $formRece = null;
        $formRefe = null;
        $formNave = null;
        $formBib = null;
        $formLect = null;
        $entryMods = null;
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($id);
        $currentlyInNav = $em->getRepository('NavegacionBundle:Navegacion')->findCurrentlyInNav($id);
        $currentlyInLect = $em->getRepository('LecturaBundle:Lectura')->findCurrentlyInLect($id);
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $filterForm = $peticion->get('form');
        switch ($modulo) {
            case 'usuario':
                $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(13);
                $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
                $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
                $tiposDoc = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(18);
                $idiomas = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(19);
                $modalidades = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(22);

                $fechaDesde = null;
                $fechaHasta = null;
                $submit = null;

                if (!is_null($filterForm) && $filterForm['filter_type'] == 'receFilters')
                    $submit = $filterForm;
                $this->loadHistoricFilters($peticion, $submit, $fechaDesde, $fechaHasta, $classActive, 'receFilters');

                $formRece = $this->createFormBuilder()
                    ->setAction(
                        $this->generateUrl('usuario_detalles', array('id' => $id, 'modulo' => 'usuario', 'page' => 1))
                    )
                    ->add(
                        'servicio',
                        'choice',
                        array(
                            'required' => 'false',
                            'empty_value' => '',
                            'choices' => $this->getChoicesArray(
                                $servicios
                            ),
                            'data' => (!is_null($submit) and array_key_exists(
                                    'servicio',
                                    $submit
                                )) ? $submit['servicio'] : '',

                        )
                    )
                    ->add(
                        'chapilla',
                        'text',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) and array_key_exists(
                                    'chapilla',
                                    $submit
                                )) ? $submit['chapilla'] : '',
                        )
                    )
                    ->add(
                        'fechaDesde',
                        'birthday',
                        array(
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'data' => !is_null($submit) ? $fechaDesde : new \DateTime(
                                '04/01/2015',
                                new \DateTimeZone('America/Havana')
                            ),
                        )
                    )
                    ->add(
                        'fechaHasta',
                        'birthday',
                        array(
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'data' => !is_null($submit) ? $fechaHasta : new \DateTime(
                                'today',
                                new \DateTimeZone('America/Havana')
                            ),
                        )
                    )
                    ->add('portlet_tab', 'hidden', array('data' => 'recepcion'))
                    ->add('filter_type', 'hidden', array('data' => 'receFilters'))
                    ->getForm();

                $fechaDesde = null;
                $fechaHasta = null;
                $submit = null;

                if (!is_null($filterForm) && $filterForm['filter_type'] == 'refeFilters')
                    $submit = $filterForm;
                $this->loadHistoricFilters($peticion, $submit, $fechaDesde, $fechaHasta, $classActive, 'refeFilters');
                $formRefe = $this->createFormBuilder()
                    ->setAction(
                        $this->generateUrl('usuario_detalles', array('id' => $id, 'modulo' => 'usuario', 'page' => 1))
                    )
                    ->add(
                        'unanswered',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'unanswered',
                                    $submit
                                )) ? $submit['unanswered'] == "1" : $this->getRequest()->get('noti') == "1",
                        )
                    )
                    ->add(
                        'desiderata',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'desiderata',
                                    $submit
                                )) ? $submit['desiderata'] == "1" : false,
                        )
                    )
                    ->add(
                        'document',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'document',
                                    $submit
                                )) ? $submit['document'] == "1" : false,
                        )
                    )
                    ->add(
                        'reference',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'reference',
                                    $submit
                                )) ? $submit['reference'] == "1" : false,
                        )
                    )
                    ->add(
                        'answer',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'answer',
                                    $submit
                                )) ? $submit['answer'] == "1" : false,
                        )
                    )
                    ->add('portlet_tab', 'hidden', array('data' => 'referencia'))
                    ->add('filter_type', 'hidden', array('data' => 'refeFilters'))
                    ->getForm();

                $fechaDesde = null;
                $fechaHasta = null;
                $submit = null;

                if (!is_null($filterForm) && $filterForm['filter_type'] == 'naveFilters')
                    $submit = $filterForm;
                $this->loadHistoricFilters($peticion, $submit, $fechaDesde, $fechaHasta, $classActive, 'naveFilters');

                $formNave = $this->createFormBuilder()
                    ->setAction(
                        $this->generateUrl('usuario_detalles', array('id' => $id, 'modulo' => 'usuario', 'page' => 1))
                    )
                    ->add(
                        'correo',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) and array_key_exists(
                                    'correo',
                                    $submit
                                )) ? $submit['correo'] == 1 : false,
                        )
                    )
                    ->add(
                        'fuentesInfo',
                        'choice',
                        array(
                            'choices' => $this->getChoicesArray(
                                $fuentesInfo
                            ),
                            'empty_value' => '',
                            'data' => (!is_null($submit) and array_key_exists(
                                    'fuentesInfo',
                                    $submit
                                )) ? $submit['fuentesInfo'] : '',
                        )
                    )
                    ->add(
                        'pc',
                        'choice',
                        array(
                            'choices' => $this->getChoicesArray(
                                $pcs
                            ),
                            'empty_value' => '',
                            'data' => (!is_null($submit) and array_key_exists('pc', $submit)) ? $submit['pc'] : '',
                        )
                    )
                    ->add(
                        'fechaDesde',
                        'birthday',
                        array(
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'data' => !is_null($submit) ? $fechaDesde : new \DateTime(
                                '04/01/2014',
                                new \DateTimeZone('America/Havana')
                            ),
                        )
                    )
                    ->add(
                        'fechaHasta',
                        'birthday',
                        array(
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'data' => !is_null($submit) ? $fechaHasta : new \DateTime(
                                'today',
                                new \DateTimeZone('America/Havana')
                            ),
                        )
                    )
                    ->add('portlet_tab', 'hidden', array('data' => 'navegacion'))
                    ->add('filter_type', 'hidden', array('data' => 'naveFilters'))
                    ->getForm();

                $fechaDesde = null;
                $fechaHasta = null;
                $submit = null;

                if (!is_null($filterForm) && $filterForm['filter_type'] == 'bibFilters')
                    $submit = $filterForm;
                $this->loadHistoricFilters($peticion, $submit, $fechaDesde, $fechaHasta, $classActive, 'bibFilters');

                $formBib = $this->createFormBuilder()
                    ->setAction(
                        $this->generateUrl('usuario_detalles', array('id' => $id, 'modulo' => 'usuario', 'page' => 1))
                    )
                    ->add('idioma', 'choice', array('choices' => $this->getChoicesArray($idiomas), 'multiple' => true))
                    ->add(
                        'tipoDocs',
                        'choice',
                        array('choices' => $this->getChoicesArray($tiposDoc), 'multiple' => true)
                    )
                    ->add(
                        'unanswered',
                        'checkbox',
                        array(
                            'required' => false,
                            'data' => (!is_null($submit) && array_key_exists(
                                    'unanswered',
                                    $submit
                                )) ? $submit['unanswered'] == "1" : $this->getRequest()->get('noti') == "1",
                        )
                    )
                    ->add(
                        'fechaDesde',
                        'birthday',
                        array(
                            'mapped' => false,
                            'data' => !is_null($submit) ? $fechaDesde : new \DateTime(
                                '04/01/2015',
                                new \DateTimeZone('America/Havana')
                            ),
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'attr' => array('style' => 'width:95%'),
                            'invalid_message' => 'El valor de este campo no es válido.',
                        )
                    )
                    ->add(
                        'fechaHasta',
                        'birthday',
                        array(
                            'mapped' => false,
                            'data' => !is_null($submit) ? $fechaHasta : new \DateTime(
                                'today',
                                new \DateTimeZone('America/Havana')
                            ),
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'attr' => array('style' => 'width:95%'),
                            'invalid_message' => 'El valor de este campo no es válido.',
                        )
                    )
                    ->add('portlet_tab', 'hidden', array('data' => 'bibliografia'))
                    ->add('filter_type', 'hidden', array('data' => 'bibFilters'))
                    ->getForm();

                $fechaDesde = null;
                $fechaHasta = null;
                $submit = null;

                if (!is_null($filterForm) && $filterForm['filter_type'] == 'lectFilters')
                    $submit = $filterForm;
                $this->loadHistoricFilters($peticion, $submit, $fechaDesde, $fechaHasta, $classActive, 'lectFilters');

                $formLect = $this->createFormBuilder()
                    ->add('modalidades', 'choice', array('choices' => $this->getChoicesArray($modalidades), 'multiple' => true))
                    ->add('detalle',
                        'text',
                        array('data' => (!is_null($submit) && array_key_exists('detalle', $submit) ? $submit['detalle'] : '')))
                    ->add(
                        'fechaDesde',
                        'birthday',
                        array(
                            'mapped' => false,
                            'data' => !is_null($submit) ? $fechaDesde : new \DateTime(
                                '04/01/2015',
                                new \DateTimeZone('America/Havana')
                            ),
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'attr' => array('style' => 'width:95%'),
                            'invalid_message' => 'El valor de este campo no es válido.',
                        )
                    )
                    ->add(
                        'fechaHasta',
                        'birthday',
                        array(
                            'mapped' => false,
                            'data' => !is_null($submit) ? $fechaHasta : new \DateTime(
                                'today',
                                new \DateTimeZone('America/Havana')
                            ),
                            'widget' => 'single_text',
                            'format' => 'dd/MM/yyyy',
                            'required' => false,
                            'attr' => array('style' => 'width:95%'),
                            'invalid_message' => 'El valor de este campo no es válido.',
                        )
                    )
                    ->add('portlet_tab', 'hidden', array('data' => 'lectura'))
                    ->add('filter_type', 'hidden', array('data' => 'lectFilters'))
                    ->getForm();
                $peticion->setSession($sesion);
                break;
            case 'recepcion':
                $recepcion = new UsuarioServicio();
                $recepcionType = new UsuarioServicioType();
                $recepcionType->setAction($this->generateUrl('recepcion_entrada'));
                $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(13);
                $recepcionType->setServicios(new ChoiceList($servicios, $servicios));
                if (count($currentlyIn) > 0) {
                    $recepcionType->setDefaultId($currentlyIn[0]->getId());
                    $recepcionType->setDefaultChapilla($currentlyIn[0]->getChapilla());
                    $recepcionType->setDefaultDocumento($currentlyIn[0]->getDocumento());
                    $recepcionType->setDefaultObservaciones($currentlyIn[0]->getObservaciones());
                    $usuario_servicio = $em->getRepository('UsuarioBundle:UsuarioServicio')->findBy(
                        array('usuario' => $currentlyIn[0]->getUsuario(), 'actual' => true)
                    );
                    $recepcionType->setDefaultServicio($usuario_servicio[0]->getServicio());
                }
                $form = $this->createForm($recepcionType, $recepcion);
                break;
            case 'bibliografia':

                $bibliografia = new Bibliografia();
                $bibliografiaType = new BibliografiaType();
                $bibliografiaType->setAction($this->generateUrl('bibliografia_salvar_solicitud'));

                $tiposDoc = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(18);
                $idiomas = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(19);
                $estilos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(20);
                $motivos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(21);

                $bibliografiaType->setTipoDocs(new ChoiceList($tiposDoc, $tiposDoc));
                $bibliografiaType->setIdiomas(new ChoiceList($idiomas, $idiomas));
                $bibliografiaType->setEstilo(new ChoiceList($estilos, $estilos));
                $bibliografiaType->setMotivo(new ChoiceList($motivos, $motivos));

                $bibliografiaType->setUsuario($usuario->getId());

                $form = $this->createForm($bibliografiaType, $bibliografia);
                break;
            case 'referencia':
                $nomencladorType = new NomencladorType();
                $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
                $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
                $nomencladorType->setFuentesInfo($choices);

                $referencia = new Referencia();
                $referenciaType = new ReferenciaType();
                $referenciaType->setAction($this->generateUrl('referencia_nueva_solicitud'));
                $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
                $referenciaType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
                $referenciaType->setFuentesInfo($nomencladorType);
                $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
                $referenciaType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
                $form = $this->createForm($referenciaType, $referencia);

                $bibliografia = new Bibliografia();
                $bibliografiaType = new BibliografiaType();
                $bibliografiaType->setAction($this->generateUrl('bibliografia_salvar_solicitud'));

                $tiposDoc = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(18);
                $idiomas = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(19);
                $estilos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(20);
                $motivos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(21);

                $bibliografiaType->setTipoDocs(new ChoiceList($tiposDoc, $tiposDoc));
                $bibliografiaType->setIdiomas(new ChoiceList($idiomas, $idiomas));
                $bibliografiaType->setEstilo(new ChoiceList($estilos, $estilos));
                $bibliografiaType->setMotivo(new ChoiceList($motivos, $motivos));
                $bibliografiaType->setReferencia(true);

                $bibliografiaType->setUsuario($usuario->getId());

                $formBib = $this->createForm($bibliografiaType, $bibliografia);
                break;
            case 'dsi':
                $nomencladorType = new NomencladorType();
                $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
                $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
                $nomencladorType->setFuentesInfo($choices);

                $dsi = new DSI();
                $dsiType = new DSIType();
                $dsiType->setAction($this->generateUrl('dsi_nueva_solicitud'));
                $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
                $dsiType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
                $dsiType->setFuentesInfo($nomencladorType);
                $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
                $dsiType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
                $form = $this->createForm($dsiType, $dsi);

                $bibliografia = new Bibliografia();
                $bibliografiaType = new BibliografiaType();
                $bibliografiaType->setAction($this->generateUrl('bibliografia_salvar_solicitud'));

                $tiposDoc = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(18);
                $idiomas = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(19);
                $estilos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(20);
                $motivos = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(21);

                $bibliografiaType->setTipoDocs(new ChoiceList($tiposDoc, $tiposDoc));
                $bibliografiaType->setIdiomas(new ChoiceList($idiomas, $idiomas));
                $bibliografiaType->setEstilo(new ChoiceList($estilos, $estilos));
                $bibliografiaType->setMotivo(new ChoiceList($motivos, $motivos));
                $bibliografiaType->setDsi(true);

                $bibliografiaType->setUsuario($usuario->getId());

                $formBib = $this->createForm($bibliografiaType, $bibliografia);
                break;
            case 'navegacion':
                $navegacionType = new NavegacionType();
                $navegacionType->setAction($this->generateUrl('navegacion_entrada'));
                $navegacion = new Navegacion();
                if (!is_null($currentlyInNav) and count($currentlyInNav) > 0) {
                    $navegacion = $currentlyInNav[0];
                    $navegacionType->setId($currentlyInNav[0]->getId());
                    $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
                } else {
                    $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
                    $currentlyInUsePc = $em->getRepository('NavegacionBundle:Navegacion')->findBy(
                        array('salida' => null)
                    );
                    foreach ($currentlyInUsePc as $cn) {
                        $key = array_search($cn->getPc(), $pcs);
                        if (!is_null($key)) {
                            array_splice($pcs, $key, 1);
                        }
                    }
                }
                $choices = new ChoiceList($pcs, $pcs);
                $navegacionType->setPcs($choices);
                $nomencladorType = new NomencladorType();
                $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
                $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
                $nomencladorType->setFuentesInfo($choices);
                $navegacionType->setFuentesInfo($nomencladorType);
                $form = $this->createForm($navegacionType, $navegacion);
                break;
            case 'lectura':
                $lecturaType = new LecturaType();
                $lecturaType->setAction($this->generateUrl('lectura_entrada'));
                $lectura = new Lectura();
                $lecturaModalidadType = new LecturaModalidadType();
                if (!is_null($currentlyInLect) and count($currentlyInLect) > 0) {
                    $lectura = $currentlyInLect[0];
                    $lecturaType->setId($currentlyInLect[0]->getId());

                }
                $modalidades = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(22);
                $choices = new ChoiceList($modalidades, $modalidades);
                $lecturaModalidadType->setModalidades($choices);
                $lecturaType->setLecturaModalidad($lecturaModalidadType);
                $form = $this->createForm($lecturaType, $lectura);
                break;
        }
        if (!is_null($usuario)) {
            if ($modulo == "dsi") {
                $modulo = "DSI";
            }

            return $this->render(
                ucfirst($modulo) . 'Bundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'active' => $classActive,
                    'form' => $form != null ? $form->createView() : $form,
                    'formRece' => $formRece != null ? $formRece->createView() : $formRece,
                    'formRefe' => $formRefe != null ? $formRefe->createView() : $formRefe,
                    'formNave' => $formNave != null ? $formNave->createView() : $formNave,
                    'formBib' => $formBib != null ? $formBib->createView() : $formBib,
                    'formLect' => $formLect != null ? $formLect->createView() : $formLect,
                    'currentlyIn' => $currentlyIn,
                    'currentlyInNav' => $currentlyInNav,
                    'currentlyInLect' => $currentlyInLect,
                    'navegacion' => isset($navegacion) ? $navegacion : null,
                )
            );
        } else {
            throw new HttpException(404, "Controlador de Usuario: El usuario no existe.");
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @internal param $basico
     */
    public
    function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('usuarioFiltros');
        $sesion->set(
            'usuarioFiltros',
            array(
                'nombres' => null,
                'apellidos' => null,
                'pais' => null,
                'email' => null,
                'telefono' => null,
                'tipoPro' => null,
                'especialidad' => null,
                'profesion' => null,
                'categOcup' => null,
                'categCien' => null,
                'categInv' => null,
                'categDoc' => null,
                'cargo' => null,
                'institucion' => null,
                'dedicacion' => null,
                'experiencia' => null,
                'tipoUsua' => null,
                'carnetBib' => null,
                'carnetId' => null,
                'fechaIns' => null,
                'fechaInsDesde' => null,
                'fechaInsHasta' => null,
                'fechaInsOper' => null,
                'atendidoPor' => null,
                'estudiante' => null,
                'inside' => null,
                'inactivo' => null,
                'currentlyInNav' => null,
                'currentlyInLect' => null,
                'orden' => null,
                'direccion' => null,
            )
        );
        $this->getRequest()->setSession($sesion);

        return new Response();

//        if ($basico == '1') {
//            return $this->redirect($this->generateUrl('usuario_lista', array('modulo' => $sesion->get('modulo'))));
//        } else {
//            return $this->redirect($this->generateUrl('busqueda_avanzada'));
//        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function avanzadaAction()
    {
        $sesion = $this->getRequest()->getSession();

        $usuario = $this->getUsuarioFromSession($sesion);

        $formulario = $this->createForm(
            $this->getUsuarioType(
                0,
                false,
                $this->generateUrl('usuario_lista', array('modulo' => $sesion->get('modulo'))),
                $this->getDoctrine()->getManager(),
                'recepcion'
            ),
            $usuario
        );

        return $this->render(
            'UsuarioBundle:Default:avanzada.html.twig',
            array(
                'formulario' => $formulario->createView(),
                'filtros' => true,
                'searchForm' => $this->getSearchForm()->createView(),
            )
        );
    }

    /**
     * @param $modulo
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function editarAction(
        $modulo,
        $id
    )
    {
        $classActive = array('sup' => $modulo, 'sub' => 'Editar');
        $usuario = $this->getDoctrine()->getManager()->find('UsuarioBundle:Usuario', $id);
        $em = $this->getDoctrine()->getManager();
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($id);
        $formulario = $this->createForm(
            $this->getUsuarioType(
                $id,
                true,
                $this->generateUrl('usuario_salvar'),
                $em,
                $modulo,
                count($currentlyIn) > 0,
                $usuario->getEstudiante(),
                true
            ),
            $usuario
        );
        $consulta = $this->getDoctrine()->getManager()->createQuery(
            'SELECT MAX(u.carnetBib)
             FROM UsuarioBundle:Usuario u
              JOIN NomencladorBundle:Nomenclador n
             WHERE u.tipoUsua =n.id
              AND n.descripcion = :tipousua'
        );
        $consulta->setParameter('tipousua', 'Temporal', 'string');
        $temporales = $consulta->getResult();
        $consulta->setParameter('tipousua', 'Potencial', 'string');
        $potenciales = $consulta->getResult();

        return $this->render(
            ucfirst($modulo) . 'Bundle:Default:editar.html.twig',
            array(
                'formulario' => !is_null($formulario) ? $formulario->createView() : null,
                'new' => false,
                'temporales' => $temporales,
                'potenciales' => $potenciales,
                'id' => $id,
                'active' => &$classActive,
                'currentlyIn' => count($currentlyIn) > 0,
                'usuario' => $usuario,


            )
        );
    }

    /**
     * @param $modulo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public
    function adicionarAction(
        $modulo
    )
    {
        $classActive = array('sup' => $modulo, 'sub' => 'Nuevo');
        $usuario = new Usuario();

        $formulario = $this->createForm(
            $this->getUsuarioType(0, true, $this->generateUrl('usuario_salvar'), $this->getDoctrine()->getManager(), $modulo),
            $usuario
        );
        $consulta = $this->getDoctrine()->getManager()->createQuery(
            'SELECT MAX(u.carnetBib)
             FROM UsuarioBundle:Usuario u
              JOIN NomencladorBundle:Nomenclador n
             WHERE u.tipoUsua =n.id
              AND n.descripcion = :tipousua'
        );
        $consulta->setParameter('tipousua', 'Temporal', 'string');
        $temporales = $consulta->getResult();
        $consulta->setParameter('tipousua', 'Potencial', 'string');
        $potenciales = $consulta->getResult();

        return $this->render(
            ucfirst($modulo) . 'Bundle:Default:editar.html.twig',
            array(
                'formulario' => $formulario->createView(),
                'new' => true,
                'searchForm' => $this->getSearchForm()->createView(),
                'active' => $classActive,
                'temporales' => $temporales,
                'potenciales' => $potenciales,
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public
    function salvarAction()
    {
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $form = $peticion->get('form');
        $classActive = array('sup' => $form['modulo'], 'sub' => 'Nuevo');
        $id = $form['id'];
        if ($id != 0) {
            $usuario = $this->getDoctrine()->getManager()->find('UsuarioBundle:Usuario', $id);
            $new = false;
        } else {
            $usuario = new Usuario();
            $new = true;
        }

        $formulario = $this->createForm(
            $this->getUsuarioType(0, true, $this->generateUrl('usuario_salvar'), $this->getDoctrine()->getManager(), $form['modulo']),
            $usuario
        );


        $formulario->handleRequest($peticion);
        if ($formulario->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if (is_null($usuario->getFechaIns())) {
                $usuario->setFechaIns(new \DateTime('now', new \DateTimeZone('America/Havana')));
            }
            if ($id == 0) {
                $usuario->setActivo(true);
            }

            $em->persist($usuario);
            $em->flush();

            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $traza->setObjeto('Usuario');
            $traza->setObservaciones(
                'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                    'usuario_detalles',
                    array('id' => $usuario->getId(), 'page' => 1, 'modulo' => $form['modulo'])
                ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
            );
            $traza->setModulo(ucfirst($form['modulo']));
            if ($id == 0) {
                $usuario->setActivo(true);
                $traza->setOperacion('Adicionar');

                $em->persist($traza);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Se han almacenado correctamente los datos del usuario'
                );
            } else {
                $traza->setOperacion('Editar');
                $em->persist($traza);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Se han modificado correctamente los datos del usuario'
                );
            }

            return $this->redirect(
                $this->generateUrl(
                    'usuario_detalles',
                    array('id' => $usuario->getId(), 'page' => 1, 'modulo' => $form['modulo'])
                )
            );

        }

        $consulta = $this->getDoctrine()->getManager()->createQuery(
            'SELECT MAX(u.carnetBib)
             FROM UsuarioBundle:Usuario u
              JOIN NomencladorBundle:Nomenclador n
             WHERE u.tipoUsua =n.id
              AND n.descripcion = :tipousua'
        );
        $consulta->setParameter('tipousua', 'Temporal', 'string');
        $temporales = $consulta->getResult();
        $consulta->setParameter('tipousua', 'Potencial', 'string');
        $potenciales = $consulta->getResult();

        return $this->render(
            ucfirst($form['modulo']) . 'Bundle:Default:editar.html.twig',
            array(
                'formulario' => $formulario->createView(),
                'new' => $new,
                'searchForm' => $this->getSearchForm()->createView(),
                'temporales' => $temporales,
                'potenciales' => $potenciales,
                'active' => $classActive,
            )
        );

    }

    /**
     * @param $id
     * @param $modulo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function eliminarAction(
        $id,
        $modulo
    )
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $usuario = $em->find('UsuarioBundle:Usuario', $id);
            if (!$usuario) {
                $this->createNotFoundException('No se encontró el usuario.');
            } else {
                $em->remove($usuario);
                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setOperacion('Eliminar');
                $traza->setObjeto('Usuario');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): ' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '. '
                );
                $traza->setModulo(ucfirst($modulo));
                $em->persist($traza);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Se han eliminado correctamente los datos del usuario'
                );
            }
        } catch (DBALException $e) {
            $this->getDoctrine()->resetManager();
            $em = $this->getDoctrine()->getManager();
            $usuario = $em->find('UsuarioBundle:Usuario', $id);
            $usuario->setActivo(false);
            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('today', new \DateTimeZone('America/Havana')));
            $traza->setModulo(ucfirst($modulo));
            $traza->setOperacion('Desactivar');
            $traza->setObjeto('Usuario');
            $traza->setObservaciones(
                'Nombre(s) y Apellido(s): ' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
            );
            $em->persist($traza);
            $em->persist($usuario);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'El usuario se encuentra en uso por registros de la aplicación. En lugar de eliminarse, será desactivado.'
            );
        }

        return $this->redirect($this->generateUrl('usuario_lista', array('modulo' => $modulo)));

    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activarAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->find('UsuarioBundle:Usuario', $id);
        $usuario->setActivo(true);
        $em->persist($usuario);
        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('today', new \DateTimeZone('America/Havana')));
        $traza->setModulo('Usuario');
        $traza->setOperacion('Desactivar');
        $traza->setObjeto('Usuario');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): ' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_edit',
            'Usuario activado exitosamente'
        );

        return $this->redirect($this->generateUrl('usuario_lista', array('modulo' => 'usuario')));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bannearAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
        $usuario->setBanned(true);
        $em->persist($usuario);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setOperacion('Prohibir Acceso');
        $traza->setObjeto('Usuario');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'usuario')
            ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $traza->setModulo('Registro');
        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_error',
            'Se ha prohibido el acceso al usuario'
        );

        return $this->redirect(
            $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'usuario')
            )
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function desbannearAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
        $usuario->setBanned(false);
        $em->persist($usuario);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setOperacion('Permitir Acceso');
        $traza->setObjeto('Usuario');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'usuario')
            ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $traza->setModulo('Registro');
        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_error',
            'Se ha prohibido el acceso al usuario'
        );

        return $this->redirect(
            $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'usuario')
            )
        );
    }

    /**
     * @param $sesion
     * @return Usuario
     */
    private
    function getUsuarioFromSession(
        $sesion
    )
    {
        $usuario = new Usuario();
        if ($sesion->has('usuarioFiltros')) {
            $filtros = $sesion->get('usuarioFiltros');
            $usuario->setNombres($filtros['nombres']);
            $usuario->setApellidos($filtros['apellidos']);
            $usuario->setEmail($filtros['email']);

//            if (!is_null($filtros['tipoPro'])) {
//                $usuario->setTipoPro(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['tipoPro'])
//                );
//            }
            if (!is_null($filtros['especialidad'])) {
                $usuario->setEspecialidad(
                    $this->getDoctrine()->getRepository(
                        'NomencladorBundle:Nomenclador'
                    )->find($filtros['especialidad'])
                );
            }
//            if (!is_null($filtros['profesion'])) {
//                $usuario->setProfesion(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['profesion'])
//                );
//            }
//            if (!is_null($filtros['categOcup'])) {
//                $usuario->setCategOcup(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['categOcup'])
//                );
//            }
//            if (!is_null($filtros['categCien'])) {
//                $usuario->setCategCien(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['categCien'])
//                );
//            }
//            if (!is_null($filtros['categInv'])) {
//                $usuario->setCategInv(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['categInv'])
//                );
//            }
//            if (!is_null($filtros['categDoc'])) {
//                $usuario->setCategDoc(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['categDoc'])
//                );
//            }
//            if (!is_null($filtros['cargo'])) {
//                $usuario->setCargo(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['cargo'])
//                );
//            }
            if (!is_null($filtros['institucion'])) {
                $usuario->setInstitucion(
                    $this->getDoctrine()->getRepository(
                        'NomencladorBundle:Nomenclador'
                    )->find($filtros['institucion'])
                );
            }
//            if (!is_null($filtros['dedicacion'])) {
//                $usuario->setDedicacion(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['dedicacion'])
//                );
//            }
//            if (!is_null($filtros['tipoUsua'])) {
//                $usuario->setTipoUsua(
//                    $this->getDoctrine()->getRepository(
//                        'NomencladorBundle:Nomenclador'
//                    )->find($filtros['tipoUsua'])
//                );
//            }
            $usuario->setPais($filtros['pais']);
            $usuario->setTipoPro($filtros['tipoPro']);
            $usuario->setProfesion($filtros['profesion']);
            $usuario->setCargo($filtros['cargo']);
            $usuario->setDedicacion($filtros['dedicacion']);
            $usuario->setCategCien($filtros['categCien']);
            $usuario->setCategDoc($filtros['categDoc']);
            $usuario->setCategInv($filtros['categInv']);
            $usuario->setCategOcup($filtros['categOcup']);
            $usuario->setEspecialidad($filtros['especialidad']);
            $usuario->setTipoUsua($filtros['tipoUsua']);
            $usuario->setExperiencia($filtros['experiencia']);
            $usuario->setTelefono($filtros['telefono']);
            $usuario->setFechaIns($filtros['fechaIns']);
            $usuario->setCarnetBib($filtros['carnetBib']);
            $usuario->setCarnetId($filtros['carnetId']);
            $usuario->setEstudiante($filtros['estudiante'] == "true");
        }

        return $usuario;
    }

    /**
     * @param $nomId
     * @param bool $activo
     * @return ChoiceList
     */
    private
    function getNomChoicesList(
        $nomId,
        $activo = true
    )
    {
        $choices = $this->getDoctrine()->getManager()->getRepository(
            'NomencladorBundle:Nomenclador'
        )->findNomencladoresFiltros(array('tiponom' => $nomId, 'activo' => $activo));
        $cuba = $this->getDoctrine()->getManager()->getRepository(
            'NomencladorBundle:Nomenclador'
        )->findBy(array('descripcion' => 'Cuba'));

        return new ChoiceList($choices, $choices, $cuba);
    }

    /**
     * @param $id
     * @param $required
     * @param $actionUrl
     * @param $manager
     * @param $modulo
     * @param bool $currentlyIn
     * @param bool $currentlyInNav
     * @param bool $currentlyInLect
     * @param bool $inactivo
     * @param bool $estudiante
     * @param bool $nomActivo
     * @return UsuarioType
     * @internal param $empty_value
     */
    private
    function getUsuarioType(
        $id,
        $required,
        $actionUrl,
        $manager,
        $modulo,
        $currentlyIn = false,
        $currentlyInNav = false,
        $currentlyInLect = false,
        $inactivo = false,
        $estudiante = false,
        $nomActivo = true
    )
    {
        $usuarioType = new UsuarioType($manager);
        $usuarioType->setId($id);
        $usuarioType->setAction($actionUrl);
        $usuarioType->setModulo($modulo);
        $usuarioType->setNombresOptions(
            array(
                'required' => $required,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Nombre no es válido.',
            )
        );
        $usuarioType->setApellidosOptions(
            array(
                'required' => $required,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Apellidos no es válido.',
            )
        );
        $usuarioType->setPaisOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo País no es válido.',
            )
        );
        $usuarioType->setEmailOptions(
            array(
                'required' => false,
                'label' => 'Correo Electrónico',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Correo Electrónico no es válido.',
            )
        );
        $usuarioType->setEstudianteOptions(
            array(
                'required' => false,
                'label' => 'Estudiante',
                'data' => $estudiante,
            )
        );
        $usuarioType->setTelefonoOptions(
            array(
                'required' => false,
                'label' => 'Teléfono',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Teléfono no es válido.',
            )
        );
        $usuarioType->setTipoProOptions(
            array(
                'required' => $required,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Tipo de Profesional no es válido.',
            )
        );
        $usuarioType->setCargoOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Cargo no es válido.',
            )
        );
        $usuarioType->setCategCienOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Categoría Científica no es válido.',
            )
        );
        $usuarioType->setCategDocOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Categoría Docente no es válido.',
            )
        );
        $usuarioType->setCategInvOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Categoía Investigativa no es válido.',
            )
        );
        $usuarioType->setCategOcupOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Categoría Ocupacional no es válido.',
            )
        );
        $usuarioType->setDedicacionOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Dedicación no es válido.',
            )
        );
        $usuarioType->setEspecialidadOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Especialidad no es válido.',
            )
        );
        $usuarioType->setInstitucionOptions(
            array(
                'required' => false,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Institución no es válido.',
            )
        );
        $usuarioType->setProfesionOptions(
            array(
                'required' => $required,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Tipo de Profesional no es válido.',
            )
        );
        $usuarioType->setExperienciaOptions(
            array(
                'required' => false,
                'label' => 'Experiencia en años',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Experiencia no es válido.',
            )
        );
        $usuarioType->setCarnetBibOptions(
            array(
                'required' => $required,
                'label' => 'Carnet de Usuario',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del número de Carnet de Usuario no es válido.',
            )
        );
        $usuarioType->setCarnetIdOptions(
            array(
                'required' => false,
                'label' => 'Carnet de Identidad',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del número de Carnet de Identidad no es válido.',
            )
        );
        $usuarioType->setFechaInsOptions(
            array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'required' => $required,
                'label' => 'Fecha de Inscripción',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor de este campo no es válido.',
            )
        );
        $usuarioType->setTipoUsuaOptions(
            array(
                'required' => $required,
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor del campo Tipo de Profesional no es válido.',
            )
        );

        $usuarioType->setObservacionesOptions(
            array(
                'required' => false,
                'label' => 'Observaciones',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor de este campo no es válido.',
            )
        );

        $usuarioType->setTemaInvOptions(
            array(
                'required' => false,
                'label' => 'Tema de Investigación',
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor de este campo no es válido.',
            )
        );

        $appUsers = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')->findAll();
        $usuarioType->setAtendidoPorOptions(
            array(
                'required' => false,
                'label' => 'Atendido Por',
                'empty_value' => '',
                'choice_list' => new ChoiceList($appUsers, $appUsers),
                'attr' => array('style' => 'width:95%'),
                'invalid_message' => 'El valor de este campo no es válido.',
            )
        );
        $usuarioType->setInsideOptions(
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'Actualmente en la biblioteca',
                'data' => $currentlyIn,
            )
        );
        $usuarioType->setCurrentlyInNavOptions(
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'Actualmente en Sala de Navegación',
                'data' => $currentlyInNav,
            )
        );
        $usuarioType->setCurrentlyInLectOptions(
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'Actualmente en Sala de Lectura',
                'data' => $currentlyInLect,
            )
        );
        $usuarioType->setInactivo(
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'Incluir inactivos',
                'data' => $inactivo,
            )
        );

        return $usuarioType;
    }

    /**
     * @param $defaultValues
     * @param $filtros
     * @param $usuario
     * @param $url
     * @return mixed
     */
    public
    function getForm(
        $defaultValues,
        $filtros,
        $usuario,
        $url
    )
    {
        $doctrineManager = $this->getDoctrine()->getManager();
        $formulario = $this->createFormBuilder(
            $usuario,
            array(
                'attr' => array(
                    'name' => 'filtros',
                    'id' => 'filtros',
                    'data_class' => 'BMN\UsuarioBundle\Entity\Usuario',
                ),
            )
        )
            ->add('tipoForm', 'hidden', array('data' => 1, 'mapped' => false))
            ->add(
                'id',
                'hidden',
                array('data' => is_array($defaultValues) ? 0 : $usuario->getId(), 'mapped' => false)
            )
            ->add(
                'nombres',
                'text',
                array(
                    'required' => $filtros == 1 ? false : true,
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) ? $defaultValues['nombres'] : $usuario->getNombres(),
                )
            )
            ->add(
                'apellidos',
                'text',
                array(
                    'required' => $filtros == 1 ? false : true,
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) ? $defaultValues['apellidos'] : $usuario->getApellidos(),
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'required' => false,
                    'label' => 'Correo Electrónico',
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) ? $defaultValues['email'] : $usuario->getEmail(),
                )
            )
            ->add(
                'telefono',
                'number',
                array(
                    'required' => false,
                    'label' => 'Teléfono',
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['telefono'])
                        ? $defaultValues['telefono'] : !is_null($usuario->getTelefono())
                            ? $usuario->getTelefono() : 0,
                )
            )
            ->add(
                'tipoPro',
                'choice',
                array(
                    'required' => $filtros == 1 ? false : true,
                    'label' => 'Tipo de Profesional',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(1)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['tipoPro'])
                        ? $defaultValues['tipoPro'] : !is_null(
                            $usuario->getTipoPro()
                        ) ? $usuario->getTipoPro()->getId() : 0,
                )
            )
            ->add(
                'especialidad',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Especialidad',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(2)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['especialidad'])
                        ? $defaultValues['especialidad'] : !is_null(
                            $usuario->getEspecialidad()
                        ) ? $usuario->getEspecialidad()->getId() : 0,
                )
            )
            ->add(
                'profesion',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Profesión',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(3)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['profesion'])
                        ? $defaultValues['profesion'] : !is_null(
                            $usuario->getProfesion()
                        ) ? $usuario->getProfesion()->getId() : 0,
                )
            )
            ->add(
                'categOcup',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Categoría Ocupacional',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(8)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['categOcup'])
                        ? $defaultValues['categOcup'] : !is_null(
                            $usuario->getCategOcup()
                        ) ? $usuario->getCategOcup()->getId() : 0,
                )
            )
            ->add(
                'categCien',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Categoría Científica',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(10)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['categCien'])
                        ? $defaultValues['categCien'] : !is_null(
                            $usuario->getCategCien()
                        ) ? $usuario->getCategCien()->getId() : 0,
                )
            )
            ->add(
                'categDoc',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Categoría Docente',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(7)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['categDoc'])
                        ? $defaultValues['categDoc'] : !is_null(
                            $usuario->getCategDoc()
                        ) ? $usuario->getCategDoc()->getId() : 0,

                )
            )
            ->add(
                'categInv',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Categoría Investigativa',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(9)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['categInv'])
                        ? $defaultValues['categInv'] : !is_null(
                            $usuario->getCategInv()
                        ) ? $usuario->getCategInv()->getId() : 0,

                )
            )
            ->add(
                'cargo',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Cargo',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(4)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['cargo'])
                        ? $defaultValues['cargo'] : !is_null(
                            $usuario->getCargo()
                        ) ? $usuario->getCargo()->getId() : 0,

                )
            )
            ->add(
                'institucion',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Institución',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(5)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['institucion'])
                        ? $defaultValues['institucion'] : !is_null(
                            $usuario->getInstitucion()
                        ) ? $usuario->getInstitucion()->getId() : 0,

                )
            )
            ->add(
                'dedicacion',
                'choice',
                array(
                    'required' => false,
                    'label' => 'Dedicación',
                    'choices' => $this->getChoicesArray(
                        $doctrineManager->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(6)
                    ),
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) && isset($defaultValues['dedicacion'])
                        ? $defaultValues['dedicacion'] : !is_null(
                            $usuario->getDedicacion()
                        ) ? $usuario->getDedicacion()->getId() : 0,
                )
            )
            ->add(
                'experiencia',
                'number',
                array(
                    'required' => false,
                    'label' => 'Experiencia',
                    'label_attr' => array('class' => 'editor-label'),
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 95%'),
                    'data' => is_array($defaultValues) ? $defaultValues['experiencia'] : !is_null(
                        $usuario->getExperiencia()
                    ) ? $usuario->getExperiencia() : 0,
                )
            )
            ->setMethod('POST')
            ->setAction($url)
            ->getForm();

        return $formulario;
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
            ->setAction($this->generateUrl('usuario_lista', array('modulo' => $sesion->get('modulo'))))
            ->setMethod('POST')
            ->getForm();

        return $searchForm;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersListAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $filtros = $filtros = $sesion->get('usuarioFiltros') != null ? $filtros = $sesion->get(
            'usuarioFiltros'
        ) : array();
        if (!array_key_exists('inactivo', $filtros) or is_null($filtros['inactivo'])) {
            $filtros['inactivo'] = 0;
        }
        if ($request->get('from') == 'navegacion' or $request->get('from') == 'lectura') {
            $filtros['inside'] = true;
        }
        switch ($request->get('from')) {
            case 'referencia':
            case 'dsi':
            case 'bibliografia':
            case 'lectura':
            case 'navegacion':
                $columnas = array(
                    'id',
                    'nombres',
                    'apellidos',
                    'carnetId',
                    'telefono',
                    'email',
                    'tipoUsua',
                    'tipoPro',
                    'institucion',
                    'carnetBib',
                    'especialidad',
                    'profesion',
                    'dedicacion',
                    'categOcup',
                    'categCien',
                    'categInv',
                    'categDoc',
                    'pais',
                    'cargo',
                    'temaInv',
                    'observaciones',
                    'atendidoPor',
                    'estudiante',
                    'fechaIns',
                );
                break;
            case 'registro':
                $columnas = array(
                    'id',
                    'nombres',
                    'apellidos',
                    'carnetId',
                    'telefono',
                    'email',
                    'tipoUsua',
                    'tipoPro',
                    'institucion',
                    'carnetBib',
                    'especialidad',
                    'profesion',
                    'dedicacion',
                    'categOcup',
                    'categCien',
                    'categInv',
                    'categDoc',
                    'pais',
                    'cargo',
                    'temaInv',
                    'observaciones',
                    'atendidoPor',
                    'estudiante',
                    'activo',
                    'fechaIns',
                );
                break;
            case 'autoservicio':
                $filtros['autoservicio'] = true;
                $columnas = array(
                    'id',
                    'nombres',
                    'apellidos',
                    'tipoUsua',
                );
                break;
            case 'recepcion':
                $columnas = array('id', 'nombres', 'apellidos', 'carnetId', 'institucion', 'carnetBib', 'fechaIns');
                break;
        }

        /* Los filtros que paso a la consulta son solo los que tengan que ver
         * con las columnas asociadas a cada vista, más valores como el currentlyInNav
         * que no están nunca como columna porque no es un dato del usuario propiamente dicho*/
        return $this->usersListAction($request,
            array_merge(
                array_intersect_key($filtros,
                    array_flip($columnas)),
                ['inside' => array_key_exists('inside', $filtros) ? $filtros['inside'] : null,
                    'currentlyInNav' => array_key_exists('currentlyInNav', $filtros) ? $filtros['currentlyInNav'] : null,
                    'currentlyInLect' => array_key_exists('currentlyInLect', $filtros) ? $filtros['currentlyInLect'] : null,
                    'inactivo' => array_key_exists('inactivo', $filtros) ? $filtros['inactivo'] : null]),
            $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersHistoricAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $filtros = $sesion->get('usuarioFiltros') != null ? $sesion->get(
            'usuarioFiltros'
        ) : array();
        if ($request->get('from') == 'navegacion') {
            $filtros['inside'] = true;
        }
        if ($request->get('from') == 'registro'
            or $request->get('from') == 'referencia'
            or $request->get('from') == 'navegacion'
        ) {
            $columnas = array(
                'id',
                'nombres',
                'apellidos',
                'carnetId',
                'telefono',
                'email',
                'tipoUsua',
                'tipoPro',
                'institucion',
                'carnetBib',
                'especialidad',
                'profesion',
                'dedicacion',
                'categOcup',
                'categCien',
                'categInv',
                'categDoc',
                'pais',
                'cargo',
                'temaInv',
                'observaciones',
                'atendidoPor',
                'fechaIns',
            );
        } else {
            $columnas = array('id', 'nombres', 'apellidos', 'institucion', 'carnetBib', 'fechaIns');
        }

        return $this->usersListAction($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \PHPExcel_Exception
     */
    public function excelExportAction()
    {
        $sesion = $this->getRequest()->getSession();
        $filters = $sesion->get('usuarioFiltros') != null ? $sesion->get(
            'usuarioFiltros'
        ) : array();
        if ($this->getRequest()->get('from') == 'navegacion') {
            $filters['inside'] = true;
        }
        $em = $this->getDoctrine()->getManager();
        $appUser = $this->get('security.context')->getToken()->getUser();
        $params = array();
        $params['start'] = $this->getRequest()->get('start');
        $params['end'] = $this->getRequest()->get('end');
        $params['length'] = $this->getRequest()->get('length');
        $params['columns'] = explode(',', $this->getRequest()->get('columns'));
        $params['visColumns'] = explode(',', $this->getRequest()->get('visColumns'));
        $order = explode(',', $this->getRequest()->get('order'));
        $params['order'][0]['column'] = $order[0];
        $params['order'][0]['dir'] = $order[1];
        $result = $em->getRepository('UsuarioBundle:Usuario')->ajaxTable($params, $filters);
        $columnCaptions = array(
            'nombres' => 'Nombre',
            'apellidos' => 'Apellidos',
            'carnetId' => 'Carnet de Identidad',
            'telefono' => 'Teléfono',
            'email' => 'Correo Electrónico',
            'tipoUsua' => 'Tipo de Usuario',
            'tipoPro' => 'Tipo de Profesional',
            'institucion' => 'Institución',
            'carnetBib' => 'Carnet de Usuario',
            'especialidad' => 'Especialidad',
            'profesion' => 'Profesión',
            'dedicacion' => 'Dedicación',
            'categOcup' => 'Categoría Ocupacional',
            'categCien' => 'Categoría Científica',
            'categDoc' => 'Categoría Docente',
            'categInv' => 'Categoría Investigativa',
            'pais' => 'País',
            'cargo' => 'Cargo',
            'temaInv' => 'Tema de Investigación',
            'observaciones' => 'Observaciones',
            'atendidoPor' => 'Atendido por',
            'fechaIns' => 'Fecha de Inscripción',
            'activo' => 'Activo',
            'estudiante' => 'Estudiante',
        );
        $excelColumns = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
        );

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("CUBiM")
            ->setLastModifiedBy($appUser->getNombre() . " " . $appUser->getApellidos())
            ->setTitle("Listado de Usuarios")
            ->setSubject("Listado de Usuarios")
            ->setDescription("Listado de Usuarios de CUBiM generado desde el módulo de Registro")
            ->setKeywords("CUBiM usuarios listado")
            ->setCategory("Listado");

        for ($i = 0; $i < count($params['visColumns']); $i++) {
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue($excelColumns[$i] . '1', $columnCaptions[$params['visColumns'][$i]]);
            $phpExcelObject->setActiveSheetIndex(0)->getStyle($excelColumns[$i] . '1')->getFont()->setBold(true);

            for ($j = 0; $j < count($result); $j++) {
                $row = $j + 2;

                if ($params['visColumns'][$i] == 'fechaIns') {
                    /* Formatear la fecha para la salida*/
                    if (is_null($result[$j][$params['visColumns'][$i]])) {
                        $data = '';
                    } else {
                        $date = getdate($result[$j][$params['visColumns'][$i]]->getTimestamp());
                        if ($date['mday'] < 10) {
                            if ($date['mon'] < 10) {
                                $data = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $data = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        } else {
                            if ($date['mon'] < 10) {
                                $data = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                            } else {
                                $data = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                            }
                        }
                    }
                } elseif ($params['visColumns'][$i] == 'atendidoPor') {
                    /* General output */
                    $data = $result[$j][$params['visColumns'][$i]] . ' ' . $result[$j]['atendidoPorApellidos'];
                } elseif ($params['visColumns'][$i] == 'activo' || $params['visColumns'][$i] == 'estudiante') {
                    $data = ($result[$j][$params['visColumns'][$i]]) ? 'Sí' : 'No';
                } elseif ($params['visColumns'][$i] != ' ') {
                    /* General output */
                    $data = $result[$j][$params['visColumns'][$i]];
                }
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue($excelColumns[$i] . $row, $data);
            }

            $phpExcelObject->setActiveSheetIndex(0)->getColumnDimension($excelColumns[$i])->setAutosize(true);
        }
        $phpExcelObject->getActiveSheet()->setTitle('Registro');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=Listado_de_Usuarios.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    public function loadHistoricFilters($request, &$submit, &$from, &$to, &$classActive, $filtersName)
    {
        $sesion = $request->getSession();
        if (is_null($submit)) {
            $submit = $sesion->get($filtersName);
        } else {
            $classActive['portlet'] = 'historial';
            $classActive['servicio'] = $submit['portlet_tab'];
        }

        $to = new \DateTime('today', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and array_key_exists('fechaHasta', $submit) and $submit['fechaHasta'] != '') {
            $fecha = explode('/', $submit['fechaHasta']);
            $to->setDate($fecha[2], $fecha[1], $fecha[0]);
        }

        $from = new \DateTime('04/01/2014', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and array_key_exists('fechaDesde', $submit) and $submit['fechaDesde'] != '') {
            $fecha = explode('/', $submit['fechaDesde']);
            $from->setDate($fecha[2], $fecha[1], $fecha[0]);
        }
        $sesion->set($filtersName, $submit);
    }
}

