<?php

namespace BMN\DSIBundle\Controller;

use BMN\BibliografiaBundle\Entity\Bibliografia;
use BMN\BibliografiaBundle\Entity\BibliografiaRespuesta;
use BMN\BibliografiaBundle\Form\BibliografiaRespuestaType;
use BMN\BibliografiaBundle\Form\BibliografiaType;
use BMN\CUBiMController;
use BMN\DSIBundle\Entity\DSI;
use BMN\DSIBundle\Form\DSIType;
use BMN\NomencladorBundle\Entity\Nomenclador;
use BMN\NomencladorBundle\Form\NomencladorType;
use BMN\OtrosBundle\Entity\Traza;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormError;


/**
 * Class DefaultController
 * @package BMN\DSIBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoPreguntasAction()
    {
        $classActive = array('sup' => 'dsi', 'sub' => 'preguntas');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $pager = $peticion->get('formPager_itemsPerPage');
        $submit = $peticion->get('form');

        if (!is_null($submit)) {
            $sesion->remove('filters');
            foreach ($submit as $clave => $valor) {
                $filters = $sesion->get('filters');
                $filters[$clave] = $valor;
                $sesion->set('filters', $filters);
            }
        } elseif ($this->getRequest()->get('noti') == "1") {
            $sesion->set('filters', array('unanswered' => true));
        }
        if (!is_null($pager) and $pager != "") {
            $sesion->set('itemsPerPage', $pager);
        }
        $filters = $sesion->get('filters');
        $solicitud = new DSI();
        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);
        $dsiType = new DSIType();
        $dsiType->setAction($this->generateUrl('dsi_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $dsiType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $dsiType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $dsiType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $formDSI = $this->createForm($dsiType, $solicitud);

        $fechaDesde = date_parse_from_format('d/m/Y', !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaDesde'] : null);
        $fechaHasta = date_parse_from_format('d/m/Y', !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaHasta'] : null);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dsi_listado_preguntas'))
            ->add(
                'unanswered',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'unanswered',
                            $filters
                        )) ? $filters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                )
            )->add(
                'desiderata',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'desiderata',
                            $filters
                        )) ? $filters['desiderata'] == "1" : false
                )
            )->add(
                'document',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'document',
                            $filters
                        )) ? $filters['document'] == "1" : false
                )
            )->add(
                'reference',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'reference',
                            $filters
                        )) ? $filters['reference'] == "1" : false
                )
            )
            ->add(
                'answer',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'answer',
                            $filters
                        )) ? $filters['answer'] == "1" : false
                )
            )
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'fechaDesde',
                            $filters
                        ) && $filters['fechaDesde'] != '') ? new \DateTime(
                        $fechaDesde['month'] . '/' . $fechaDesde['day'] . '/' . $fechaDesde['year'],
                        new \DateTimeZone('America/Havana')
                    ) : new \DateTime('04/01/2015', new \DateTimeZone('America/Havana')),
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add(
                'fechaHasta',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'fechaHasta',
                            $filters
                        ) && $filters['fechaHasta'] != '') ? new \DateTime(
                        $fechaHasta['month'] . '/' . $fechaHasta['day'] . '/' . $fechaHasta['year'],
                        new \DateTimeZone('America/Havana')
                    ) : new \DateTime('today', new \DateTimeZone('America/Havana')),
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add(
                'order',
                'hidden',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'order',
                            $filters
                        )) ? $filters['order'] : null
                )
            )
            ->add(
                'dir',
                'hidden',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'dir',
                            $filters
                        )) ? $filters['dir'] : null
                )
            )
            ->add(
                'search',
                'text',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'search',
                            $filters
                        )) ? $filters['search'] : null
                )
            );

        $peticion->setSession($sesion);

        return $this->render(
            'DSIBundle:Default:listadoPreguntas.html.twig',
            array(
                'active' => $classActive,
                'formFiltros' => $form->getForm()->createView(),
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'form' => $formDSI->createView(),
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPreguntasAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $from = $request->get('from');
        $id = $request->get('amp;id');
        $filtros = $sesion->get('filters') != null ? $sesion->get('filters') : array();
        if ($from == 'userDetails') {
            $columnas = array(
                'atendidoPor',
                'pregunta',
                'viaSolicitud',
                'respuesta',
                'tipo',
                'fuentesInfo',
                'adjunto',
                'desiderata',
                'fechaSolicitud',
                'fechaRespuesta'
            );
            $filtros['usuario'] = $id;
        } else {
            $columnas = array(
                'id',
                'usuario',
                'atendidoPor',
                'pregunta',
                'viaSolicitud',
                'respuesta',
                'tipo',
                'fuentesInfo',
                'adjunto',
                'desiderata',
                'fechaSolicitud',
                'fechaRespuesta'
            );
        }

        return $this->getPreguntasDSI($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function nuevaSolicitudAction()
    {
        $em = $this->getDoctrine()->getManager();
        $valores = $this->getRequest()->get('formDSI');
        if ($valores['id'] != null and $valores['id'] > 0) {
            $solicitud = $em->getRepository('DSIBundle:DSI')->find($valores['id']);
            $solicitud->restartFuentesInfo();
        } else {
            $solicitud = new DSI();
        }

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);
        $dsiType = new DSIType();
        $dsiType->setAction($this->generateUrl('dsi_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $dsiType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $dsiType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $dsiType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $form = $this->createForm($dsiType, $solicitud);
        $temp = $solicitud->getPath();
        $form->handleRequest($this->getRequest());
        if (!is_null($solicitud->getFile())
            and $temp != $solicitud->getName() . '.' . $solicitud->getFile()->guessExtension()
            and $this->get('filesystem')->exists(__DIR__ . '/../../../../web/reference/attachments/' . $solicitud->getName() . '.' . $solicitud->getFile()->guessExtension())
        )
            $form->addError(new FormError('El nombre de archivo "' . $solicitud->getName() . '.' . $solicitud->getFile()->guessExtension() . '" ya ha sido utilizado. Cambie el nombre del adjunto.'));
        if (is_null($solicitud->getPregunta()))
            $form->addError(new FormError('El campo "Texto de pregunta" no puede quedar vacío.'));
        if ($form->isValid()) {
            $fuentesInfoCorrected = new ArrayCollection();
            foreach ($solicitud->getFuentesInfo() as $fuenteInfo) {
                if ($fuenteInfo instanceof Nomenclador) {
                    $fuentesInfoCorrected->add($fuenteInfo->getId());
                }
            }
            $solicitud->getFuentesInfo()->clear();
            foreach ($fuentesInfoCorrected as $fuenteInfo) {
                $solicitud->getFuentesInfo()->add($fuenteInfo);
            }
            $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($valores['usuario']);
            if ($valores['id'] != null and $valores['id'] > 0) {
                if ($valores['respuesta'] != null and $valores['respuesta'] != "" and $solicitud->getFechaRespuesta() == null
                ) {
                    $solicitud->setFechaRespuesta(new \DateTime('now', new \DateTimeZone('America/Havana')));
                }
            } else {
                $solicitud->setAppUser($this->get('security.context')->getToken()->getUser());
                $solicitud->setFechaSolicitud(new \DateTime('now', new \DateTimeZone('America/Havana')));
                if ($valores['respuesta'] != null and $valores['respuesta'] != "") {
                    $solicitud->setFechaRespuesta(new \DateTime('now', new \DateTimeZone('America/Havana')));
                }
            }
            $solicitud->setDocumento(false);
            $solicitud->setReferencia(false);
            $solicitud->setVerbal(false);
            if (array_key_exists('tipoRespuesta', $valores)) {
                foreach ($valores['tipoRespuesta'] as $tipoRes) {
                    switch ($tipoRes) {
                        case '0':
                            $solicitud->setReferencia(true);
                            break;
                        case '1':
                            $solicitud->setDocumento(true);
                            break;
                        case '2':
                            $solicitud->setVerbal(true);
                            break;
                    }
                }
            }
            $solicitud->setUsuario($usuario);
            if ($this->getRequest()->files != null) {
                try {
                    $solicitud->preUpload();
                    $solicitud->upload();
                } catch (Exception $e) {
                    $this->get('session')->getFlashBag()->add(
                        'info_delete',
                        $e
                    );
                    goto linea1;
                }
            }
            $em->persist($solicitud);
            $em->flush();
            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $traza->setModulo('DSI');
            $traza->setObjeto('Solicitud de DSI');
            if ($valores['id'] != null and $valores['id'] > 0) {
                $traza->setOperacion('Editar');
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Se ha modificado correctamente la solicitud'
                );
            } else {
                $traza->setOperacion('Insertar');
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Se ha insertado correctamente la solicitud'
                );
            }
            if (is_null($usuario)) {
                $traza->setObservaciones(
                    'Pregunta sin usuario'
                );
            } else {
                $traza->setObservaciones(
                    'Usuario: <a href="' . $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'dsi')
                    ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                );
            }
            $traza->setModulo('DSI');
            $em->persist($traza);
            $em->flush();
            if ($valores['id'] != null and $valores['id'] > 0) {
                return $this->redirect($this->generateUrl('dsi_listado_preguntas'));
            } elseif ($valores['usuario'] == null or $valores['usuario'] <= 0) {
                return $this->redirect($this->generateUrl('dsi_listado_preguntas'));
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'dsi', 'page' => 1)
                    )
                );
            }
        }
        linea1:
        $usuario_id = $this->getRequest()->get('formDSI');
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($usuario_id['usuario']);
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario_id['usuario']);
        if ($valores['id'] != null or $valores['id'] < 0) {
            $classActive = array('sup' => 'dsi', 'sub' => 'preguntas');
            $sesion = $this->getRequest()->getSession();
            $filters = $sesion->get('filters');
            $fechaDesde = date_parse_from_format('d/m/Y', !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaDesde'] : null);
            $fechaHasta = date_parse_from_format('d/m/Y', !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaHasta'] : null);
            $formFiltros = $this->createFormBuilder()
                ->setAction($this->generateUrl('dsi_listado_preguntas'))
                ->add(
                    'unanswered',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'unanswered',
                                $filters
                            )) ? $filters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                    )
                )->add(
                    'desiderata',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'desiderata',
                                $filters
                            )) ? $filters['desiderata'] == "1" : false
                    )
                )->add(
                    'document',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'document',
                                $filters
                            )) ? $filters['document'] == "1" : false
                    )
                )->add(
                    'reference',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'reference',
                                $filters
                            )) ? $filters['reference'] == "1" : false
                    )
                )
                ->add(
                    'answer',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'answer',
                                $filters
                            )) ? $filters['answer'] == "1" : false
                    )
                )
                ->add(
                    'fechaDesde',
                    'birthday',
                    array(
                        'mapped' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'fechaDesde',
                                $filters
                            ) && $filters['fechaDesde'] != '') ? new \DateTime(
                            $fechaDesde['month'] . '/' . $fechaDesde['day'] . '/' . $fechaDesde['year'],
                            new \DateTimeZone('America/Havana')
                        ) : new \DateTime('04/01/2015', new \DateTimeZone('America/Havana')),
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'required' => false,
                        'attr' => array('style' => 'width:95%'),
                        'invalid_message' => 'El valor de este campo no es válido.'
                    )
                )
                ->add(
                    'fechaHasta',
                    'birthday',
                    array(
                        'mapped' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'fechaHasta',
                                $filters
                            ) && $filters['fechaHasta'] != '') ? new \DateTime(
                            $fechaHasta['month'] . '/' . $fechaHasta['day'] . '/' . $fechaHasta['year'],
                            new \DateTimeZone('America/Havana')
                        ) : new \DateTime('today', new \DateTimeZone('America/Havana')),
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'required' => false,
                        'attr' => array('style' => 'width:95%'),
                        'invalid_message' => 'El valor de este campo no es válido.'
                    )
                )
                ->add(
                    'order',
                    'hidden',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'order',
                                $filters
                            )) ? $filters['order'] : null
                    )
                )
                ->add(
                    'dir',
                    'hidden',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'dir',
                                $filters
                            )) ? $filters['dir'] : null
                    )
                )
                ->add(
                    'search',
                    'text',
                    array(
                        'required' => false,
                        'data' => (!is_null($filters) && array_key_exists(
                                'search',
                                $filters
                            )) ? $filters['search'] : null
                    )
                );
            $preguntas = $em->getRepository('DSIBundle:DSI')->findAll();

            return $this->render(
                'DSIBundle:Default:listadoPreguntas.html.twig',
                array(
                    'active' => $classActive,
                    'formFiltros' => $formFiltros->getForm()->createView(),
                    'preguntas' => $preguntas,
                    'form' => $form->createView(),
                )
            );
        } else {
            $classActive = array('sup' => 'dsi', 'sub' => 'usuarios');

            return $this->render(
                'DSIBundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'page' => 1,
                    'active' => $classActive,
                    'form' => $form != null ? $form->createView() : $form,
                    'currentlyIn' => $currentlyIn,
                )
            );
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modificarSolicitudAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $solicitud = $em->getRepository('DSIBundle:DSI')->find($id);

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);

        $dsiType = new DSIType();
        $dsiType->setAction($this->generateUrl('dsi_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $dsiType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $dsiType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $dsiType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $dsiType->setUsuario(!is_null($solicitud->getUsuario()) ? $solicitud->getUsuario()->getId() : null);
        $form = $this->createForm($dsiType, $solicitud);

        return $this->render(
            'DSIBundle:Default:solicitud.html.twig',
            array(
                'form' => $form->createView(),
                'solicitud' => $solicitud
            )
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function eliminarSolicitudAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $solicitud = $em->getRepository('DSIBundle:DSI')->find($id);

        $em->remove($solicitud);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setModulo('DSI');
        $traza->setObjeto('Solicitud de DSI');
        $traza->setOperacion('Eliminar');

        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Se ha eliminado correctamente la solicitud'
        );

        return $this->redirect($this->generateUrl('dsi_listado_preguntas'));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function borrarAdjuntoAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $solicitud = $em->getRepository('DSIBundle:DSI')->find($id);

        $solicitud->removeUpload();
        $solicitud->setName('');
        $solicitud->setPath('');
        $em->persist($solicitud);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setModulo('DSI');
        $traza->setObjeto('Solicitud de DSI');
        $traza->setOperacion('Editar');

        $em->persist($traza);

        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'info_edit',
            'Se ha modificado correctamente la solicitud'
        );

        return $this->listadoPreguntasAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('filters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoPreguntasAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoSolicitudesAction()
    {
        $classActive = array('sup' => 'dsi', 'sub' => 'solicitudes');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $submit = $peticion->get('form');

        if (!is_null($submit)) {
            $sesion->remove('bibFilters');
            foreach ($submit as $clave => $valor) {
                $filters = $sesion->get('bibFilters');
                $filters[$clave] = $valor;
                $sesion->set('bibFilters', $filters);
            }
        } elseif ($this->getRequest()->get('noti') == "1") {
            $sesion->set('bibFilters', array('unanswered' => true));
        }
        $filters = $sesion->get('bibFilters');

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
        $formBibliografia = $this->createForm($bibliografiaType, $bibliografia);

        $fechaDesde = date_parse_from_format(
            'd/m/Y',
            !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaDesde'] : null
        );
        $fechaHasta = date_parse_from_format(
            'd/m/Y',
            !is_null($filters) && array_key_exists('fechaDesde', $filters) ? $filters['fechaHasta'] : null
        );
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dsi_listado_solicitudes'))
            ->add('tema', 'text',
                array(
                    'required' => false,
                    'data' => !is_null($filters) && array_key_exists('tema', $filters) ? $filters['tema'] : ''))
            ->add('idioma', 'choice', array('choices' => $this->getChoicesArray($idiomas), 'multiple' => true))
            ->add('tipoDocs', 'choice', array('choices' => $this->getChoicesArray($tiposDoc), 'multiple' => true))
            ->add(
                'unanswered',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'unanswered',
                            $filters
                        )) ? $filters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                )
            )
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'fechaDesde',
                            $filters
                        ) && $filters['fechaDesde'] != '') ? new \DateTime(
                        $fechaDesde['month'] . '/' . $fechaDesde['day'] . '/' . $fechaDesde['year'],
                        new \DateTimeZone('America/Havana')
                    ) : new \DateTime('04/01/2015', new \DateTimeZone('America/Havana')),
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es v?lido.'
                )
            )
            ->add(
                'fechaHasta',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($filters) && array_key_exists(
                            'fechaHasta',
                            $filters
                        ) && $filters['fechaHasta'] != '') ? new \DateTime(
                        $fechaHasta['month'] . '/' . $fechaHasta['day'] . '/' . $fechaHasta['year'],
                        new \DateTimeZone('America/Havana')
                    ) : new \DateTime('today', new \DateTimeZone('America/Havana')),
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es v?lido.'
                )
            );
        $peticion->setSession($sesion);

        $biblioRespuesta = new BibliografiaRespuesta();
        $biblioRespType = new BibliografiaRespuestaType();
        $biblioRespType->setAction($this->generateUrl('bibliografia_respuesta'));

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);

        $biblioRespType->setFuentesInfo($nomencladorType);
        $formBiblioRespuesta = $this->createForm($biblioRespType, $biblioRespuesta);

        return $this->render(
            'DSIBundle:Default:listadoSolicitudes.html.twig',
            array(
                'active' => $classActive,
                'formFiltros' => $form->getForm()->createView(),
                'form' => $formBibliografia->createView(),
                'formBiblioRespuesta' => $formBiblioRespuesta->createView()
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarSolicitudesFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('bibFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoSolicitudesAction();
    }
}
