<?php

namespace BMN\RecepcionBundle\Controller;

use BMN\CUBiMController;
use BMN\UsuarioBundle\Form\UsuarioServicioType;
use BMN\UsuarioBundle\Entity\UsuarioServicio;
use Exception;
use BMN\RecepcionBundle\Entity\Recepcion;
use BMN\OtrosBundle\Entity\Traza;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

/**
 * Class DefaultController
 * @package BMN\RecepcionBundle\Controller
 */
class DefaultController extends CUBiMController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoEntradasAction()
    {
        $classActive = array('sup' => 'recepcion', 'sub' => 'entradas');
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $submit = $peticion->get('form');

        if (is_null($submit)) {
            $submit = $sesion->get('receFilters');
        }

        $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and $submit['fechaHasta'] != '') {
            $fecha = explode('/', $submit['fechaHasta']);
            $fechaHasta->setDate($fecha[2], $fecha[1], $fecha[0]);
        }

        $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and $submit['fechaDesde'] != '') {
            $fecha = explode('/', $submit['fechaDesde']);
            $fechaDesde->setDate($fecha[2], $fecha[1], $fecha[0]);
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('recepcion_listado_entradas'))
            ->add(
                'nombres',
                'text',
                array(
                    'required' => false,
                    'data' => (!is_null($submit) and array_key_exists('nombres', $submit)) ? $submit['nombres'] : '',
                )
            )
            ->add(
                'apellidos',
                'text',
                array(
                    'required' => false,
                    'data' => (!is_null($submit) and array_key_exists(
                            'apellidos',
                            $submit
                        )) ? $submit['apellidos'] : '',
                )
            )
            ->add(
                'chapilla',
                'text',
                array(
                    'required' => false,
                    'data' => (!is_null($submit) and array_key_exists('chapilla', $submit)) ? $submit['chapilla'] : '',
                )
            )
            ->add(
                'carnetId',
                'text',
                array(
                    'required' => false,
                    'data' => !is_null($submit) and array_key_exists('carnetId', $submit) ? $submit['carnetId'] : '',
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
                        'today',
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
            );
        $sesion->set('receFilters', $submit);
        $peticion->setSession($sesion);

        return $this->render(
            'RecepcionBundle:Default:listadoEntradas.html.twig',
            array(
                'active' => $classActive,
                'form' => $form->getForm()->createView(),
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('receFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoEntradasAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEntradasAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $from = $request->get('from');
        $id = $request->get('id');
        $filtros = $sesion->get('receFilters') != null ? $sesion->get('receFilters') : array(
            "fechaDesde" => null,
            "fechaHasta" => null,
        );
        if ($from == 'userDetails') {
            $columnas = array(
                'fecha',
                'servicio',
                'chapilla',
                'observaciones',
                'entrada',
                'salida',
            );
            if (!array_key_exists('fechaDesde', $filtros) or is_null($filtros['fechaDesde'])) {
                $filtros['fechaDesde'] = '01/04/2015';
            }
            $filtros['usuario'] = $id;
        } else {
            $columnas = array(
                'fecha',
                'nombres',
                'apellidos',
                'carnetId',
                'servicio',
                'chapilla',
                'observaciones',
                'entrada',
                'salida',
            );
        }

        return $this->getRecepcionEntradas($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function entradaAction()
    {
        $classActive = array('sup' => 'recepcion', 'sub' => 'Lista');
        $peticion = $this->getRequest();
        $usuario_id = $peticion->get('formRecepcion');
        $em = $this->getDoctrine()->getManager();
        $usuarioServicioType = new UsuarioServicioType();
        $usuarioServicioType->setAction($this->generateUrl('recepcion_entrada'));
        $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(13);
        $usuarioServicioType->setServicios(new ChoiceList($servicios, $servicios));
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario_id['usuario']);
        $currentlyInNav = $em->getRepository('NavegacionBundle:Navegacion')->findCurrentlyInNav($usuario_id['usuario']);
        $currentlyInLect = $em->getRepository('LecturaBundle:Lectura')->findCurrentlyInLect($usuario_id['usuario']);
        if ((is_null($usuario_id['id']) or $usuario_id['id'] == '') and count($currentlyIn) > 0) {
            return $this->redirect(
                $this->generateUrl(
                    'usuario_detalles',
                    array('id' => $usuario_id['usuario'], 'modulo' => 'recepcion', 'page' => 1)
                )
            );
        } else {
            if (is_null($usuario_id['id']) or $usuario_id['id'] == '') {
                $recepcion = new Recepcion();
                $usuarioServicio = new UsuarioServicio();
            } else {
                $recepcion = $em->getRepository('RecepcionBundle:Recepcion')->find($usuario_id['id']);

                $usuarioServicio = $em->getRepository('UsuarioBundle:UsuarioServicio')->findBy(
                    array('usuario' => $recepcion->getUsuario(), 'actual' => true)
                );
                $usuarioServicio = $usuarioServicio[0];
                $usuarioServicio->setUsuario(null);
            }
            $form = $this->createForm($usuarioServicioType, $usuarioServicio);

            $form->handleRequest($this->getRequest());
            $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($usuario_id['usuario']);
            if ($form->isValid()) {
                $recepcion->setUsuario($usuario);
                $recepcion->setDocumento($usuario_id['documento']);
                if (is_null($usuario_id['chapilla']) or $usuario_id['chapilla'] == '') {
                    $recepcion->setChapilla(0);
                } else {
                    $recepcion->setChapilla($usuario_id['chapilla']);
                }
                $recepcion->setObservaciones($usuario_id['observaciones']);
                if (is_null($usuario_id['id']) or $usuario_id['id'] == '') {
                    $recepcion->setEntrada(new \DateTime('now', new \DateTimeZone('America/Havana')));
                }
                $usuarioServicio->setUsuario($usuario);
                $usuarioServicio->setActual(true);
                if (is_null($usuario_id['id']) or $usuario_id['id'] == '') {
                    $usuarioServicio->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                }
                $em->persist($recepcion);
                $em->persist($usuarioServicio);

                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre().' '.$this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setOperacion('Dar Entrada');
                $traza->setObjeto('Usuario');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): <a href="'.$this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'recepcion')
                    ).'">'.$usuario->getNombres().' '.$usuario->getApellidos().'</a>. '
                );
                $traza->setModulo('Recepcion');
                $em->persist($traza);

                $em->flush();
//                $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario->getId());
//
//
//                $usuarioServicioType->setDefaultId($recepcion->getId());
//                $usuarioServicioType->setDefaultChapilla($recepcion->getChapilla());
//                $usuarioServicioType->setDefaultDocumento($recepcion->getDocumento());
//                $usuarioServicioType->setDefaultObservaciones($recepcion->getObservaciones());
//                $usuarioServicioType->setDefaultServicio($usuarioServicio->getServicio());
//                $usuarioServicio->setUsuario(null);
//                $form = $this->createForm($usuarioServicioType, $usuarioServicio);

                return $this->redirect(
                    $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'recepcion', 'page' => 1)
                    )
                );
            }

            return $this->render(
                'RecepcionBundle:Default:detalles.html.twig',
                array(
                    'usuario' => $usuario,
                    'page' => 1,
                    'active' => $classActive,
                    'form' => $form->createView(),
                    'currentlyIn' => $currentlyIn,
                    'currentlyInNav' => $currentlyInNav,
                    'currentlyInLect' => $currentlyInLect,
                )
            );
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function salidaMasivaAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entradas = $em->getRepository('RecepcionBundle:Recepcion')->findBy(array('salida' => null));
        try {
            foreach ($entradas as $entrada) {
                $usuario = $entrada->getUsuario();
                $navegacion = $em->getRepository('NavegacionBundle:Navegacion')->findBy(
                    array('usuario' => $usuario, 'salida' => null)
                );
                if (count($navegacion) > 0) {
                    try {
                        if (is_null($navegacion[0]->getSalida())) {
                            $navegacion[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));

                            $em->persist($navegacion[0]);
                        }
                    } catch (Exception $e) {
                        ;
                    }
                }
                $lectura = $em->getRepository('LecturaBundle:Lectura')->findBy(
                    array('usuario' => $usuario, 'salida' => null)
                );
                if (count($lectura) > 0) {
                    try {
                        if (is_null($lectura[0]->getSalida())) {
                            $lectura[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));

                            $em->persist($lectura[0]);
                        }
                    } catch (Exception $e) {
                        ;
                    }
                }
                $recepcion = $em->getRepository('RecepcionBundle:Recepcion')->findBy(
                    array('usuario' => $usuario, 'salida' => null)
                );
                $recepcion[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $em->persist($recepcion[0]);

                $usuarioServicio = $em->getRepository('UsuarioBundle:UsuarioServicio')->findBy(
                    array('usuario' => $recepcion[0]->getUsuario(), 'actual' => true)
                );
                $usuarioServicio[0]->setActual(false);
                $em->persist($usuarioServicio[0]);

                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre().' '.$this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setOperacion('Dar Salida');
                $traza->setObjeto('Usuario');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): <a href="'.$this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'recepcion')
                    ).'">'.$usuario->getNombres().' '.$usuario->getApellidos().'</a>. '
                );
                $traza->setModulo('Recepcion');
                $em->persist($traza);
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_add',
                'Se ha dado salida exitosamente a todos los usuarios que se encontraban en la biblioteca.'
            );
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'info_error',
                'No fue posible dar salida a los usuarios de manera masiva.'
            );
        }

        return $this->redirect($this->generateUrl('usuario_lista', array('modulo' => 'recepcion')));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function salidaAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
        $navegacion = $em->getRepository('NavegacionBundle:Navegacion')->findBy(
            array('usuario' => $usuario, 'salida' => null)
        );
        if (count($navegacion) > 0) {
            try {
                if (is_null($navegacion[0]->getSalida())) {
                    $navegacion[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));

                    $em->persist($navegacion[0]);
                }
            } catch (Exception $e) {
                ;
            }
        }
        $lectura = $em->getRepository('LecturaBundle:Lectura')->findBy(
            array('usuario' => $usuario, 'salida' => null)
        );
        if (count($lectura) > 0) {
            try {
                if (is_null($lectura[0]->getSalida())) {
                    $lectura[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));

                    $em->persist($lectura[0]);
                }
            } catch (Exception $e) {
                ;
            }
        }
        $recepcion = $em->getRepository('RecepcionBundle:Recepcion')->findBy(
            array('usuario' => $usuario, 'salida' => null)
        );
        $recepcion[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $em->persist($recepcion[0]);

        $usuarioServicio = $em->getRepository('UsuarioBundle:UsuarioServicio')->findBy(
            array('usuario' => $recepcion[0]->getUsuario(), 'actual' => true)
        );
        $usuarioServicio[0]->setActual(false);
        $em->persist($usuarioServicio[0]);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre().' '.$this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setOperacion('Dar Salida');
        $traza->setObjeto('Usuario');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): <a href="'.$this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'recepcion')
            ).'">'.$usuario->getNombres().' '.$usuario->getApellidos().'</a>. '
        );
        $traza->setModulo('Recepcion');
        $em->persist($traza);

        $em->flush();
        $usuarioServicio = new UsuarioServicio();
        $usuarioServicioType = new UsuarioServicioType();
        $usuarioServicioType->setAction($this->generateUrl('recepcion_entrada'));
        $servicios = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(13);
        $usuarioServicioType->setServicios(new ChoiceList($servicios, $servicios));
        $form = $this->createForm($usuarioServicioType, $usuarioServicio);
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario->getId());

        return $this->redirect(
            $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'modulo' => 'recepcion', 'page' => 1)
            )
        );
    }
}
