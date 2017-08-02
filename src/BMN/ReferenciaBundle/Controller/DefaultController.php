<?php

namespace BMN\ReferenciaBundle\Controller;

use BMN\BibliografiaBundle\Entity\Bibliografia;
use BMN\BibliografiaBundle\Entity\BibliografiaRespuesta;
use BMN\BibliografiaBundle\Form\BibliografiaRespuestaType;
use BMN\BibliografiaBundle\Form\BibliografiaType;
use BMN\CUBiMController;
use BMN\NomencladorBundle\Entity\Nomenclador;
use BMN\NomencladorBundle\Form\NomencladorType;
use BMN\OtrosBundle\Entity\Traza;
use BMN\ReferenciaBundle\Entity\Referencia;
use BMN\ReferenciaBundle\Form\ReferenciaType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class DefaultController
 * @package BMN\ReferenciaBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoPreguntasAction()
    {
        $classActive = array('sup' => 'referencia', 'sub' => 'preguntas');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $pager = $peticion->get('formPager_itemsPerPage');
        $submit = $peticion->get('form');

        if (!is_null($submit)) {
            $sesion->remove('refeFilters');
            foreach ($submit as $clave => $valor) {
                $refeFilters = $sesion->get('refeFilters');
                $refeFilters[$clave] = $valor;
                $sesion->set('refeFilters', $refeFilters);
            }
        } elseif ($this->getRequest()->get('noti') == "1") {
            $sesion->set('refeFilters', array('unanswered' => true));
        }
        if (!is_null($pager) and $pager != "") {
            $sesion->set('itemsPerPage', $pager);
        }
        $refeFilters = $sesion->get('refeFilters');
        $solicitud = new Referencia();
        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);
        $referenciaType = new ReferenciaType();
        $referenciaType->setAction($this->generateUrl('referencia_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $referenciaType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $referenciaType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $referenciaType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $formReferencia = $this->createForm($referenciaType, $solicitud);

        $fechaDesde = date_parse_from_format('d/m/Y', !is_null($refeFilters) && array_key_exists('fechaDesde', $refeFilters) ? $refeFilters['fechaDesde'] : null);
        $fechaHasta = date_parse_from_format('d/m/Y', !is_null($refeFilters) && array_key_exists('fechaDesde', $refeFilters) ? $refeFilters['fechaHasta'] : null);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('referencia_listado_preguntas'))
            ->add(
                'unanswered',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'unanswered',
                            $refeFilters
                        )) ? $refeFilters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                )
            )->add(
                'desiderata',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'desiderata',
                            $refeFilters
                        )) ? $refeFilters['desiderata'] == "1" : false
                )
            )->add(
                'document',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'document',
                            $refeFilters
                        )) ? $refeFilters['document'] == "1" : false
                )
            )->add(
                'reference',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'reference',
                            $refeFilters
                        )) ? $refeFilters['reference'] == "1" : false
                )
            )
            ->add(
                'answer',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'answer',
                            $refeFilters
                        )) ? $refeFilters['answer'] == "1" : false
                )
            )
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'fechaDesde',
                            $refeFilters
                        ) && $refeFilters['fechaDesde'] != '') ? new \DateTime(
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
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'fechaHasta',
                            $refeFilters
                        ) && $refeFilters['fechaHasta'] != '') ? new \DateTime(
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
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'order',
                            $refeFilters
                        )) ? $refeFilters['order'] : null
                )
            )
            ->add(
                'dir',
                'hidden',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'dir',
                            $refeFilters
                        )) ? $refeFilters['dir'] : null
                )
            )
            ->add(
                'search',
                'text',
                array(
                    'required' => false,
                    'data' => (!is_null($refeFilters) && array_key_exists(
                            'search',
                            $refeFilters
                        )) ? $refeFilters['search'] : null
                )
            );

        $peticion->setSession($sesion);

        return $this->render(
            'ReferenciaBundle:Default:listadoPreguntas.html.twig',
            array(
                'active' => $classActive,
                'formFiltros' => $form->getForm()->createView(),
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'form' => $formReferencia->createView(),
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
        $id = $request->get('id');
        $filtros = $sesion->get('refeFilters') != null ? $sesion->get('refeFilters') : array();
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

        return $this->getPreguntas($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function nuevaSolicitudAction()
    {
        $em = $this->getDoctrine()->getManager();
        $valores = $this->getRequest()->get('formReferencia');
        if ($valores['id'] != null and $valores['id'] > 0) {
            $solicitud = $em->getRepository('ReferenciaBundle:Referencia')->find($valores['id']);
            $solicitud->restartFuentesInfo();
        } else {
            $solicitud = new Referencia();
        }

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);
        $referenciaType = new ReferenciaType();
        $referenciaType->setAction($this->generateUrl('referencia_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $referenciaType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $referenciaType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $referenciaType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $form = $this->createForm($referenciaType, $solicitud);
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
            $traza->setModulo('Referencia');
            $traza->setObjeto('Solicitud de Referencia');
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
                        array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'referencia')
                    ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                );
            }
            $traza->setModulo('Referencia');
            $em->persist($traza);
            $em->flush();
            if ($valores['id'] != null and $valores['id'] > 0) {
                return $this->redirect($this->generateUrl('referencia_listado_preguntas'));
            } elseif ($valores['usuario'] == null or $valores['usuario'] <= 0) {
                return $this->redirect($this->generateUrl('referencia_listado_preguntas'));
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'referencia', 'page' => 1)
                    )
                );
            }
        }
        linea1:
        $usuario_id = $this->getRequest()->get('formReferencia');
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($usuario_id['usuario']);
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario_id['usuario']);
        if ($valores['id'] != null or $valores['id'] < 0) {
            $classActive = array('sup' => 'referencia', 'sub' => 'preguntas');
            $sesion = $this->getRequest()->getSession();
            $refeFilters = $sesion->get('refeFilters');
            $fechaDesde = date_parse_from_format('d/m/Y', !is_null($refeFilters) && array_key_exists('fechaDesde', $refeFilters) ? $refeFilters['fechaDesde'] : null);
            $fechaHasta = date_parse_from_format('d/m/Y', !is_null($refeFilters) && array_key_exists('fechaDesde', $refeFilters) ? $refeFilters['fechaHasta'] : null);
            $formFiltros = $this->createFormBuilder()
                ->setAction($this->generateUrl('referencia_listado_preguntas'))
                ->add(
                    'unanswered',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'unanswered',
                                $refeFilters
                            )) ? $refeFilters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                    )
                )->add(
                    'desiderata',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'desiderata',
                                $refeFilters
                            )) ? $refeFilters['desiderata'] == "1" : false
                    )
                )->add(
                    'document',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'document',
                                $refeFilters
                            )) ? $refeFilters['document'] == "1" : false
                    )
                )->add(
                    'reference',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'reference',
                                $refeFilters
                            )) ? $refeFilters['reference'] == "1" : false
                    )
                )->add(
                    'answer',
                    'checkbox',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'answer',
                                $refeFilters
                            )) ? $refeFilters['answer'] == "1" : false
                    )
                )
                ->add(
                    'fechaDesde',
                    'birthday',
                    array(
                        'mapped' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'fechaDesde',
                                $refeFilters
                            ) && $refeFilters['fechaDesde'] != '') ? new \DateTime(
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
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'fechaHasta',
                                $refeFilters
                            ) && $refeFilters['fechaHasta'] != '') ? new \DateTime(
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
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'order',
                                $refeFilters
                            )) ? $refeFilters['order'] : null
                    )
                )
                ->add(
                    'dir',
                    'hidden',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'dir',
                                $refeFilters
                            )) ? $refeFilters['dir'] : null
                    )
                )
                ->add(
                    'search',
                    'text',
                    array(
                        'required' => false,
                        'data' => (!is_null($refeFilters) && array_key_exists(
                                'search',
                                $refeFilters
                            )) ? $refeFilters['search'] : null
                    )
                );
            $preguntas = $em->getRepository('ReferenciaBundle:Referencia')->findAll();
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

            if (!is_null($usuario))
                $bibliografiaType->setUsuario($usuario->getId());

            $formBib = $this->createForm($bibliografiaType, $bibliografia);
            return $this->render(
                'ReferenciaBundle:Default:listadoPreguntas.html.twig',
                array(
                    'active' => $classActive,
                    'formFiltros' => $formFiltros->getForm()->createView(),
                    'preguntas' => $preguntas,
                    'form' => $form->createView(),
                    'formBib' => $formBib->createView()
                )
            );
        } else {
            $classActive = array('sup' => 'referencia', 'sub' => 'usuarios');
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
            return $this->render(
                'ReferenciaBundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'page' => 1,
                    'active' => $classActive,
                    'form' => $form != null ? $form->createView() : $form,
                    'currentlyIn' => $currentlyIn,
                    'formBib' => $formBib->createView()
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
        $solicitud = $em->getRepository('ReferenciaBundle:Referencia')->find($id);

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);

        $referenciaType = new ReferenciaType();
        $referenciaType->setAction($this->generateUrl('referencia_nueva_solicitud'));
        $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
        $referenciaType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
        $referenciaType->setFuentesInfo($nomencladorType);
        $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
        $referenciaType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
        $referenciaType->setUsuario(!is_null($solicitud->getUsuario()) ? $solicitud->getUsuario()->getId() : null);
        $form = $this->createForm($referenciaType, $solicitud);

        return $this->render(
            'ReferenciaBundle:Default:solicitud.html.twig',
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

        $solicitud = $em->getRepository('ReferenciaBundle:Referencia')->find($id);

        $em->remove($solicitud);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setModulo('Referencia');
        $traza->setObjeto('Solicitud de Referencia');
        $traza->setOperacion('Eliminar');

        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Se ha eliminado correctamente la solicitud'
        );

        return $this->redirect($this->generateUrl('referencia_listado_preguntas'));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function borrarAdjuntoAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $solicitud = $em->getRepository('ReferenciaBundle:Referencia')->find($id);

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
        $traza->setModulo('Referencia');
        $traza->setObjeto('Solicitud de Referencia');
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

        $sesion->remove('refeFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoPreguntasAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoSolicitudesAction()
    {
        $classActive = array('sup' => 'referencia', 'sub' => 'solicitudes');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $submit = $peticion->get('form');

        if (!is_null($submit)) {
            $sesion->remove('bibFilters');
            foreach ($submit as $clave => $valor) {
                $bibFilters = $sesion->get('bibFilters');
                $bibFilters[$clave] = $valor;
                $sesion->set('bibFilters', $bibFilters);
            }
        } elseif ($this->getRequest()->get('noti') == "1") {
            $sesion->set('bibFilters', array('unanswered' => true));
        }
        $bibFilters = $sesion->get('bibFilters');

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
        $formBibliografia = $this->createForm($bibliografiaType, $bibliografia);

        $fechaDesde = date_parse_from_format(
            'd/m/Y',
            !is_null($bibFilters) && array_key_exists('fechaDesde', $bibFilters) ? $bibFilters['fechaDesde'] : null
        );
        $fechaHasta = date_parse_from_format(
            'd/m/Y',
            !is_null($bibFilters) && array_key_exists('fechaDesde', $bibFilters) ? $bibFilters['fechaHasta'] : null
        );
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('referencia_listado_solicitudes'))
            ->add('tema', 'text',
                array(
                    'required' => false,
                    'data' => !is_null($bibFilters) && array_key_exists('tema', $bibFilters) ? $bibFilters['tema'] : ''))
            ->add('idioma', 'choice', array('choices' => $this->getChoicesArray($idiomas), 'multiple' => true))
            ->add('tipoDocs', 'choice', array('choices' => $this->getChoicesArray($tiposDoc), 'multiple' => true))
            ->add(
                'unanswered',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($bibFilters) && array_key_exists(
                            'unanswered',
                            $bibFilters
                        )) ? $bibFilters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1"
                )
            )
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($bibFilters) && array_key_exists(
                            'fechaDesde',
                            $bibFilters
                        ) && $bibFilters['fechaDesde'] != '') ? new \DateTime(
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
                    'data' => (!is_null($bibFilters) && array_key_exists(
                            'fechaHasta',
                            $bibFilters
                        ) && $bibFilters['fechaHasta'] != '') ? new \DateTime(
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
            'ReferenciaBundle:Default:listadoSolicitudes.html.twig',
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
