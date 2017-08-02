<?php

namespace BMN\BibliografiaBundle\Controller;

use BMN\CUBiMController;
use BMN\BibliografiaBundle\Entity\Bibliografia;
use BMN\BibliografiaBundle\Entity\BibliografiaNomenclador;
use BMN\BibliografiaBundle\Entity\BibliografiaRespuesta;
use BMN\BibliografiaBundle\Form\BibliografiaRespuestaType;
use BMN\BibliografiaBundle\Form\BibliografiaType;
use BMN\NomencladorBundle\Entity\Nomenclador;
use BMN\NomencladorBundle\Form\NomencladorType;
use BMN\OtrosBundle\Entity\Traza;
use BMN\ReferenciaBundle\Entity\Referencia;
use BMN\ReferenciaBundle\Form\ReferenciaType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package BMN\BibliografiaBundle\Controller
 */
class DefaultController extends CUBiMController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoSolicitudesAction()
    {
        $classActive = array('sup' => 'bibliografia', 'sub' => 'preguntas');
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
            ->setAction($this->generateUrl('bibliografia_listado_solicitudes'))
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
                        )) ? $bibFilters['unanswered'] == "1" : $this->getRequest()->get('noti') == "1",
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
                    'invalid_message' => 'El valor de este campo no es válido.',
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
                    'invalid_message' => 'El valor de este campo no es válido.',
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
            'BibliografiaBundle:Default:listadoSolicitudes.html.twig',
            array(
                'active' => $classActive,
                'formFiltros' => $form->getForm()->createView(),
                'form' => $formBibliografia->createView(),
                'formBiblioRespuesta' => $formBiblioRespuesta->createView(),
            )
        );
    }

    /**
     * @param $id
     * @param null $userId
     * @return Response
     */
    public function editarSolicitudAction($id = null, $userId = null)
    {
        $classActive = array('sup' => 'bibliografia', 'sub' => '');
        $em = $this->getDoctrine()->getManager();
        if (!is_null($id) and $id != "null") {
            $bibliografia = $em->getRepository('BibliografiaBundle:Bibliografia')->find($id);
            $bibliografiaNomenclador = $em->getRepository(
                'BibliografiaBundle:BibliografiaNomenclador'
            )->findByBibliografia(
                $id
            );
        } else {
            $bibliografia = new Bibliografia();
            if (!is_null($userId) and $userId != "null") {
                $bibliografia->setUsuario($em->getRepository('UsuarioBundle:Usuario')->find($userId));
            }
            $bibliografiaNomenclador = null;
        }
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
        $bibliografiaType->setUsuario($bibliografia->getUsuario()->getId());
        $bibliografiaType->setId($bibliografia->getId());
        $bibliografiaType->setReferencia($bibliografia->isReferencia());
        $bibliografiaType->setDsi($bibliografia->isDsi());
        $formBibliografia = $this->createForm($bibliografiaType, $bibliografia);

        return $this->render(
            'BibliografiaBundle:Default:solicitud.html.twig',
            array(
                'form' => $formBibliografia->createView(),
                'solicitud' => $bibliografia,
                'idiomasTiposDocs' => $bibliografiaNomenclador,
                'active' => $classActive
            )
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function eliminarSolicitudAction($id)
    {
        $url = $this->generateUrl('bibliografia_listado_solicitudes');
        $em = $this->getDoctrine()->getManager();

        $solicitud = $em->getRepository('BibliografiaBundle:Bibliografia')->find($id);
        if ($solicitud->isReferencia())
            $url = $this->generateUrl('referencia_listado_solicitudes');
        if ($solicitud->isDsi())
            $url = $this->generateUrl('dsi_listado_solicitudes');

        $nomencladoresAsoc = $em->getRepository('BibliografiaBundle:BibliografiaNomenclador')->findBy(
            array('bibliografia' => $id)
        );

        foreach ($nomencladoresAsoc as $nA) {
            $em->remove($nA);
        }
        try {
            $em->remove($solicitud);

            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $traza->setModulo('Bibliografía');
            $traza->setObjeto('Solicitud de Bibliografía');
            $traza->setOperacion('Eliminar');

            $em->persist($traza);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Se ha eliminado correctamente la solicitud'
            );
        } catch (DBALException $e) {
            $this->get('session')->getFlashBag()->add(
                'info_error',
                'No se ha podido eliminar la solicitud porque tiene respuestas asociadas'
            );
        }

        return $this->redirect($url);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function salvarSolicitudAction()
    {
        $em = $this->getDoctrine()->getManager();
        $valores = $this->getRequest()->get('formBibliografia');
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($valores['usuario']);
        $bibliografiaNomencladores = null;

        if (is_null($valores['id']) or $valores['id'] == "") {
            $bibliografia = new Bibliografia();
        } else {
            $bibliografia = $em->getRepository('BibliografiaBundle:Bibliografia')->find($valores['id']);
            $bibliografiaNomencladores = $em->getRepository(
                'BibliografiaBundle:BibliografiaNomenclador'
            )->findByBibliografia($valores['id']);
        }
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

        $form = $this->createForm($bibliografiaType, $bibliografia);

        $form->handleRequest($this->getRequest());
        if (count($bibliografia->getIdiomas()) == 0) {
            $form->addError(new FormError("Debe escoger los idiomas en que desea la respuesta a la solicitud"));
        }
        if (count($bibliografia->getTiposDocs()) == 0) {
            $form->addError(
                new FormError("Debe escoger los tipos de documentos en que desea la respuesta a la solicitud")
            );
        }
        if ($form->isValid()) {
            $appUser = $this->get('security.context')->getToken()->getUser();
            if ($appUser != "anon.") {
                $bibliografia->setAppUser($appUser);
            } else {
                $sql = $em->createQuery(
                    "SELECT MAX(bib.fechaSolicitud), appUser.id
                        FROM BibliografiaBundle:Bibliografia bib LEFT JOIN bib.appUser appUser
                                LEFT JOIN appUser.roles role
                         WHERE role.role = 'ROLE_BIBLIOGRAFIA'"
                );

                $lastBib = $sql->getSingleResult();

                if (!is_null($lastBib)) {
                    $otherAppUsers = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')
                        ->createQueryBuilder('a')
                        ->select('a')
                        ->leftJoin('a.roles', 'roles')
                        ->where('a.id <> ' . $lastBib['id'])
                        ->andWhere('roles.role = :role')
                        ->getQuery();
                    $otherAppUsers->setParameter(':role', 'ROLE_BIBLIOGRAFIA');

                    $result = $otherAppUsers->getResult();
                    $bibliografia->setAppUser($result[array_rand($result)]);
                } else {
                    $otherAppUsers = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')
                        ->createQueryBuilder('a')
                        ->select('a')
                        ->leftJoin('a.roles', 'roles')
                        ->andWhere('roles.role = :role')
                        ->getQuery();
                    $otherAppUsers->setParameter(':role', 'ROLE_BIBLIOGRAFIA');
                    $result = $otherAppUsers->getResult();
                    $bibliografia->setAppUser($result[array_rand($result)]);
                }
                $bibliografia->setAutoservicio(true);
            }
            if (is_null($valores['id']) or $valores['id'] == "") {
                $bibliografia->setFechaSolicitud(new \DateTime('now', new \DateTimeZone('America/Havana')));
            }
            $bibliografia->setUsuario($usuario);
            $em->persist($bibliografia);
            $em->flush();

            if (!is_null($bibliografiaNomencladores) and count($bibliografiaNomencladores) > 0) {
                foreach ($bibliografiaNomencladores as $bN) {
                    $assoc = false;
                    foreach ($bibliografia->getIdiomas() as $idioma) {
                        if ($idioma->getId() == $bN->getNomenclador()->getId()) {
                            $assoc = true;
                        }
                    }
                    foreach ($bibliografia->getTiposDocs() as $tipoDoc) {
                        if ($tipoDoc->getId() == $bN->getNomenclador()->getId()) {
                            $assoc = true;
                        }
                    }
                    if (!$assoc) {
                        $em->remove($bN);
                    }
                }
                foreach ($bibliografia->getIdiomas() as $idioma) {
                    $assoc = false;
                    foreach ($bibliografiaNomencladores as $bN) {
                        if ($idioma->getId() == $bN->getNomenclador()->getId()) {
                            $assoc = true;
                        }
                    }
                    if (!$assoc) {
                        $bibliografiaNomenclador = new BibliografiaNomenclador();
                        $bibliografiaNomenclador->setBibliografia($bibliografia);
                        $bibliografiaNomenclador->setNomenclador($idioma);
                        $em->persist($bibliografiaNomenclador);
                    }
                }
                foreach ($bibliografia->getTiposDocs() as $tipoDoc) {
                    $assoc = false;
                    foreach ($bibliografiaNomencladores as $bN) {
                        if ($tipoDoc->getId() == $bN->getNomenclador()->getId()) {
                            $assoc = true;
                        }
                    }
                    if (!$assoc) {
                        $bibliografiaNomenclador = new BibliografiaNomenclador();
                        $bibliografiaNomenclador->setBibliografia($bibliografia);
                        $bibliografiaNomenclador->setNomenclador($tipoDoc);
                        $em->persist($bibliografiaNomenclador);
                    }
                }
            } else {
                foreach ($bibliografia->getIdiomas() as $idioma) {
                    $bibliografiaNomenclador = new BibliografiaNomenclador();
                    $bibliografiaNomenclador->setBibliografia($bibliografia);
                    $bibliografiaNomenclador->setNomenclador($idioma);
                    $em->persist($bibliografiaNomenclador);
                }

                foreach ($bibliografia->getTiposDocs() as $tipoDoc) {
                    $bibliografiaNomenclador = new BibliografiaNomenclador();
                    $bibliografiaNomenclador->setBibliografia($bibliografia);
                    $bibliografiaNomenclador->setNomenclador($tipoDoc);
                    $em->persist($bibliografiaNomenclador);
                }
            }
            $em->flush();
            if (is_null($valores['id']) or $valores['id'] == "") {
                $traza = new Traza();
                if ($appUser != "anon.") {
                    $traza->setAppUser(
                        $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                            'security.context'
                        )->getToken()->getUser()->getApellidos()
                    );
                } else {
                    $traza->setAppUser("Anónimo");
                }
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Bibliografía');
                $traza->setObjeto('Solicitud de Bibliografía');
                $traza->setOperacion('Insertar');
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Se ha insertado correctamente la solicitud'
                );
                $traza->setObservaciones(
                    'Usuario: <a href="' . $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'bibliografia')
                    ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                );
                $traza->setModulo('Bibliografía');
                $em->persist($traza);
                $em->flush();

                if ($appUser != "anon.") {
                    if ($valores['referencia'] == 1) {
                        $modulo = 'referencia';
                    } elseif ($valores['referencia'] == 1) {
                        $modulo = 'referencia';
                    } elseif ($valores['dsi'] == 1) {
                        $modulo = 'dsi';
                    } else {
                        $modulo = 'bibliografia';
                    }

                    return $this->redirect(
                        $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $usuario->getId(), 'modulo' => $modulo, 'page' => 1)
                        )
                    );
                } else {
                    return $this->redirect($this->generateUrl('bibliografia_auto_servicio'));
                }
            } else {
                $traza = new Traza();
                if ($appUser != "anon.") {
                    $traza->setAppUser(
                        $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                            'security.context'
                        )->getToken()->getUser()->getApellidos()
                    );
                } else {
                    $traza->setAppUser("Anónimo");
                }
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Bibliografía');
                $traza->setObjeto('Solicitud de Bibliografía');
                $traza->setOperacion('Modificar');
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Se ha modificado correctamente la solicitud'
                );
                $traza->setObservaciones(
                    'Usuario: <a href="' . $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'bibliografia')
                    ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                );
                $traza->setModulo('Bibliografía');
                $em->persist($traza);
                $em->flush();

                if ($bibliografia->isReferencia()) {
                    return $this->redirect($this->generateUrl('referencia_listado_solicitudes'));
                } else if ($bibliografia->isDsi()) {
                    return $this->redirect($this->generateUrl('dsi_listado_solicitudes'));
                } else {
                    return $this->redirect($this->generateUrl('bibliografia_listado_solicitudes'));
                }
            }
        }
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario->getId());

        if ($bibliografia->isReferencia()) {
            $classActive = array('sup' => 'referencia', 'sub' => 'usuarios');

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
            $formRefe = $this->createForm($referenciaType, $referencia);

            return $this->render(
                'ReferenciaBundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'page' => 1,
                    'active' => $classActive,
                    'form' => $formRefe != null ? $formRefe->createView() : $formRefe,
                    'formBib' => $form != null ? $form->createView() : $form,
                    'currentlyIn' => $currentlyIn,
                )
            );
        } elseif ($bibliografia->isDsi()) {
            $classActive = array('sup' => 'dsi', 'sub' => 'usuarios');

            $nomencladorType = new NomencladorType();
            $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
            $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
            $nomencladorType->setFuentesInfo($choices);

            $dsi = new Referencia();
            $dsiType = new ReferenciaType();
            $dsiType->setAction($this->generateUrl('dsi_nueva_solicitud'));
            $viasSolic = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(14);
            $dsiType->setViaSolicitud(new ChoiceList($viasSolic, $viasSolic));
            $dsiType->setFuentesInfo($nomencladorType);
            $tiposRes = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(16);
            $dsiType->setTipoRespuesta(new ChoiceList($tiposRes, $tiposRes));
            $formDSI = $this->createForm($dsiType, $dsi);

            return $this->render(
                'DSIBundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'page' => 1,
                    'active' => $classActive,
                    'form' => $formDSI != null ? $formDSI->createView() : $formDSI,
                    'formBib' => $form != null ? $form->createView() : $form,
                    'currentlyIn' => $currentlyIn,
                )
            );
        }
        {
            $classActive = array('sup' => 'bibliografia', 'sub' => 'usuarios');

            return $this->render(
                'BibliografiaBundle:Default:detalles.html.twig',
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adicionarRespuestaAction()
    {
        $em = $this->getDoctrine()->getManager();
        $valores = $this->getRequest()->get('formBiblioRespuesta');

        $bibliografia = $em->getRepository('BibliografiaBundle:Bibliografia')->find($valores['bibliografia']);

        if (is_null($valores['id']) or $valores['id'] == "") {
            $biblioRespuesta = new BibliografiaRespuesta();
        } else {
            $biblioRespuesta = $em->getRepository('BibliografiaBundle:BibliografiaRespuesta')->find($valores['id']);
            foreach ($biblioRespuesta->getFuentesInfo() as $fuenteInfo) {
                $biblioRespuesta->removeFuentesInfo($fuenteInfo);
            }
        }
        $biblioRespType = new BibliografiaRespuestaType();
        $biblioRespType->setAction($this->generateUrl('bibliografia_respuesta'));
        $biblioRespType->setBibliografia($bibliografia->getId());

        $nomencladorType = new NomencladorType();
        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $choices = new ChoiceList($fuentesInfo, $fuentesInfo);
        $nomencladorType->setFuentesInfo($choices);

        $biblioRespType->setFuentesInfo($nomencladorType);
        $formBiblioRespuesta = $this->createForm($biblioRespType, $biblioRespuesta);

        $formBiblioRespuesta->handleRequest($this->getRequest());
        if ($formBiblioRespuesta->isValid()) {
            $fuentesInfoCorrected = new ArrayCollection();
            foreach ($biblioRespuesta->getFuentesInfo() as $fuenteInfo) {
                if ($fuenteInfo instanceof Nomenclador) {
                    $fuentesInfoCorrected->add($fuenteInfo->getId());
                }
            }
            $biblioRespuesta->getFuentesInfo()->clear();
            foreach ($fuentesInfoCorrected as $fuenteInfo) {
                $biblioRespuesta->getFuentesInfo()->add($fuenteInfo);
            }
            $biblioRespuesta->setBibliografia($bibliografia);
            $biblioRespuesta->setFechaRespuesta(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $biblioRespuesta->setAppUser($this->get('security.context')->getToken()->getUser());
            $em->persist($biblioRespuesta);

            if (is_null($bibliografia->getAppUser())) {
                $bibliografia->setAppUser($this->get('security.context')->getToken()->getUser());
                $em->persist($bibliografia);
            }
            $em->flush();
            if (is_null($valores['id']) or $valores['id'] == "") {
                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Bibliografía');
                $traza->setObjeto('Respuesta de Bibliografía');
                $traza->setOperacion('Insertar');
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Se ha insertado correctamente la respuesta'
                );
                $traza->setObservaciones(
                    'Usuario: <a href="' . $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $bibliografia->getUsuario()->getId(), 'modulo' => 'bibliografia')
                    ) . '">' . $bibliografia->getUsuario()->getNombres() . ' ' . $bibliografia->getUsuario()->getApellidos() . '</a>. '
                );
                $traza->setModulo('Bibliografía');
                $em->persist($traza);
                $em->flush();
            } else {
                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Bibliografía');
                $traza->setObjeto('Respuesta de Bibliografía');
                $traza->setOperacion('Modificar');
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Se ha modificado correctamente la respuesta'
                );
                $traza->setObservaciones(
                    'Usuario: <a href="' . $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $bibliografia->getUsuario()->getId(), 'modulo' => 'bibliografia')
                    ) . '">' . $bibliografia->getUsuario()->getNombres() . ' ' . $bibliografia->getUsuario()->getApellidos() . '</a>. '
                );
                $traza->setModulo('Bibliografía');
                $em->persist($traza);
                $em->flush();
            }

            if ($bibliografia->isReferencia()) {
                return $this->redirect($this->generateUrl('referencia_listado_solicitudes'));
            }
            if ($bibliografia->isDsi()) {
                return $this->redirect($this->generateUrl('dsi_listado_solicitudes'));
            }

            return $this->redirect($this->generateUrl('bibliografia_listado_solicitudes'));
        }
    }

    /**
     * @param $id
     * @return Response
     */
    public function respuestasAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $respuestas = $em->getRepository('BibliografiaBundle:BibliografiaRespuesta')->findByBibliografia($id);
        $resultado = array();
        $fila = array();
        foreach ($respuestas as $respuesta) {
            $fila['id'] = $respuesta->getId();
            $fila['bibliografia'] = $id;
            $fila['descriptores'] = $respuesta->getDescriptores();
            $fila['respondidoPor'] = !is_null($respuesta->getAppUser()) ? $respuesta->getAppUser()->getNombre() . ' ' . $respuesta->getAppUser()->getApellidos() : '';
            $fila['citas'] = $respuesta->getCitas();
            $fila['citasRelevantes'] = $respuesta->getCitasRelevantes();
            $fila['citasPertinentes'] = $respuesta->getCitasPertinentes();
            $fila['observaciones'] = $respuesta->getObservaciones();
            $date = getdate($respuesta->getFechaRespuesta()->getTimestamp());
            if ($date['mday'] < 10) {
                if ($date['mon'] < 10) {
                    if ($date['hours'] < 10) {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    } else {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    }
                } else {
                    if ($date['hours'] < 10) {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    } else {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    }
                }
            } else {
                if ($date['mon'] < 10) {
                    if ($date['hours'] < 10) {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    } else {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    }
                } else {
                    if ($date['hours'] < 10) {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  0' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    } else {
                        if ($date['minutes'] < 10) {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . '0' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        } else {
                            if ($date['seconds'] < 10) {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . '0' . $date['seconds'];
                            } else {
                                $fila['fechaRespuesta'] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'] . '  ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
                            }
                        }
                    }
                }
            };
            $fuentes = '';
            foreach ($respuesta->getFuentesInfo() as $fuente) {
                if ($fuentes != '') {
                    $fuentes = $fuentes . ', ' . $fuente->getDescripcion();
                } else {
                    $fuentes = $fuente->getDescripcion();
                }
            }
            $fila['fuentesInfo'] = $fuentes;
            $resultado['data'][] = $fila;
        }

        return new Response(json_encode($resultado));
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSolicitudesAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $from = $request->get('from');
        $id = $request->get('id');
        $filtros = $sesion->get('bibFilters') != null ? $filtros = $sesion->get(
            'bibFilters'
        ) : array();
        if (array_key_exists('portlet_tab', $filtros)) {
            unset($filtros['portlet_tab']);
        }
        if ($request->get('from') == 'referencia') {
            $filtros['referencia'] = true;
        }
        if ($request->get('from') == 'dsi') {
            $filtros['dsi'] = true;
        }
        if ($from == 'userDetails') {
            $columnas = array(
                'id',
                'appUser',
                'tema',
                'motivo',
                'annos',
                'estilo',
                'idiomas',
                'tiposDocs',
                'fechaSolicitud',
            );
            $filtros['usuario'] = $id;
        } else {
            $columnas = array(
                'id',
                'usuario',
                'appUser',
                'tema',
                'motivo',
                'annos',
                'estilo',
                'idiomas',
                'tiposDocs',
                'fechaSolicitud',
            );
        }

        return $this->getBibSolicitudes($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('bibFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoSolicitudesAction();
    }

    /**
     * @param $id
     * @return Response
     */
    public function exportarModeloAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $bibliografiaRespuesta = $em->getRepository('BibliografiaBundle:BibliografiaRespuesta')->find($id);
        $bibliografia = $em->getRepository('BibliografiaBundle:Bibliografia')->find(
            $bibliografiaRespuesta->getBibliografia()->getId()
        );
        $citas = preg_split("/[\n]+/", $bibliografiaRespuesta->getCitas());

        $pdf = $this->container->get("white_october.tcpdf")->create();
// set document information
        $pdf->SetCreator('CUBiM');
        $pdf->SetAuthor(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $pdf->SetTitle('Modelo de Respuesta de Bibliografía');
        $pdf->SetSubject('Respuesta de Bibliografía');
        $pdf->SetKeywords('Bibliografía, PDF, Respuesta');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 12, '', true);
        $pdf->AddPage();
        $pdf->setTextShadow(
            array(
                'enabled' => false,
                'depth_w' => 0.2,
                'depth_h' => 0.2,
                'color' => array(196, 196, 196),
                'opacity' => 1,
                'blend_mode' => 'Normal',
            )
        );

        $html = $this->render(
            'BibliografiaBundle:Default:modeloRespuesta.html.twig',
            array('bibliografia' => $bibliografia, 'bibliografiaRespuesta' => $bibliografiaRespuesta, 'citas' => $citas)
        );

        $pdf->writeHTML($html->getContent());

        $citasSplited = array_chunk($citas, 7 * 2);
        foreach ($citasSplited as $citaSplited) {
            ;
            $pdf->AddPage();
            $html = $this->render(
                'BibliografiaBundle:Default:citas.html.twig',
                array(
                    'citas' => $citaSplited,
                    'bibliografia' => $bibliografia,
                )
            );
            $pdf->writeHTML($html->getContent());
        }

        $pdf->AddPage();
        $html = $this->render('BibliografiaBundle:Default:modeloRetro.html.twig');
        $pdf->writeHTML($html->getContent());

        return new Response(
            $pdf->Output(),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="respuesta.pdf"',
            )
        );
    }

    /**
     * @return Response
     */
    public function autoServicioAction()
    {
        return $this->render('BibliografiaBundle:Default:autoServicio.html.twig');
    }
}
