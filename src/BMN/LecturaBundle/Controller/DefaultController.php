<?php

namespace BMN\LecturaBundle\Controller;

use BMN\CUBiMController;
use BMN\LecturaBundle\Entity\Lectura;
use BMN\LecturaBundle\Entity\LecturaModalidad;
use BMN\LecturaBundle\Form\LecturaModalidadType;
use BMN\LecturaBundle\Form\LecturaType;
use BMN\OtrosBundle\Entity\Traza;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package BMN\LecturaBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @return Response
     */
    public function listadoEntradasAction()
    {
        $classActive = array('sup' => 'lectura', 'sub' => 'entradas');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $submit = $peticion->get('form');

        if (!is_null($submit)) {
            $sesion->remove('lectFilters');
            foreach ($submit as $clave => $valor) {
                $lectFilters = $sesion->get('lectFilters');
                $lectFilters[$clave] = $valor;
                $sesion->set('lectFilters', $lectFilters);
            }
        }
        $lectFilters = $sesion->get('lectFilters');

        $fechaDesde = date_parse_from_format(
            'd/m/Y',
            !is_null($lectFilters) && array_key_exists('fechaDesde', $lectFilters) ? $lectFilters['fechaDesde'] : null
        );
        $fechaHasta = date_parse_from_format(
            'd/m/Y',
            !is_null($lectFilters) && array_key_exists('fechaDesde', $lectFilters) ? $lectFilters['fechaHasta'] : null
        );
        $modalidades = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(22);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('lectura_listado_entradas'))
            ->add('modalidades', 'choice', array('choices' => $this->getChoicesArray($modalidades), 'multiple' => true))
            ->add('detalle', 'text', array('data' => (!is_null($lectFilters) && array_key_exists('detalle', $lectFilters) ? $lectFilters['detalle'] : '')))
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'data' => (!is_null($lectFilters) && array_key_exists(
                            'fechaDesde',
                            $lectFilters
                        ) && $lectFilters['fechaDesde'] != '') ? new \DateTime(
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
                    'data' => (!is_null($lectFilters) && array_key_exists(
                            'fechaHasta',
                            $lectFilters
                        ) && $lectFilters['fechaHasta'] != '') ? new \DateTime(
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

        return $this->render(
            'LecturaBundle:Default:listadoEntradas.html.twig',
            array(
                'active' => $classActive,
                'formFiltros' => $form->getForm()->createView()
            )
        );
    }

    /**
     * @return Response
     */
    public function getEntradasAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $from = $request->get('from');
        $id = $request->get('id');
        $filtros = $filtros = $sesion->get('lectFilters') != null ? $filtros = $sesion->get(
            'lectFilters'
        ) : array();
        if ($from == 'userDetails') {
            $columnas = array(
                'id',
                'entrada',
                'salida'
            );
            $filtros['usuario_id'] = $id;
        } else {
            $columnas = array(
                'id',
                'usuario',
                'entrada',
                'salida'
            );
        }

        return $this->getLecturaEntradas($request, $filtros, $columnas);
    }

    /**
     * @param $id
     * @return Response
     */
    public function getModalidadDetallesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $lecturaModalidades = $em->getRepository('LecturaBundle:Lectura')->findDetallesByLectura($id);
        $resultado = array();
        $fila = array();
        foreach ($lecturaModalidades as $lecturaModalidad) {
            if ($lecturaModalidad->getModalidad()->getDescripcion() == "Autoestudio") {
                $fila['id'] = null;
                $fila['modalidad'] = $lecturaModalidad->getModalidad()->getDescripcion();
                $fila['detalle'] = null;
                $fila['tipo'] = null;
                $resultado['data'][] = $fila;
            } else {
                $modalidadDetalles = $lecturaModalidad->getModalidadDetalle();
                foreach ($modalidadDetalles as $modalidadDetalle) {
                    $fila['id'] = $modalidadDetalle->getId();
                    $fila['modalidad'] = $lecturaModalidad->getModalidad()->getDescripcion();
                    $fila['detalle'] = $modalidadDetalle->getDetalle();
                    $fila['tipo'] = ucfirst($modalidadDetalle->getTipo());
                    $resultado['data'][] = $fila;
                }
            }
        }

        return new Response(json_encode($resultado));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function entradaAction()
    {
        $classActive = array('sup' => 'lectura', 'sub' => 'Lista');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $formLectura = $peticion->get('formLectura');
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($formLectura['usuario']);
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario->getId());
        $lecturaType = new LecturaType();
        $lecturaType->setAction($this->generateUrl('lectura_entrada'));

        if (!is_null($formLectura['id']) and $formLectura['id'] != "") {
            $lectura = $em->getRepository('LecturaBundle:Lectura')->find($formLectura['id']);
        } else {
            $lectura = new Lectura();
        }

        $lecturaModalidadType = new LecturaModalidadType();
        $modalidades = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(22);
        $choices = new ChoiceList($modalidades, $modalidades);
        $lecturaModalidadType->setModalidades($choices);
        $lecturaType->setLecturaModalidad($lecturaModalidadType);
        $form = $this->createForm($lecturaType, $lectura);

        if (count($currentlyIn) > 0) {
            $form->handleRequest($peticion);
            #region Validaciones
            if ($lectura->getLecturaModalidad()->count() >= 1) {
                foreach ($lectura->getLecturaModalidad() as $lecturaModalidad) {
                    $counter = 0;
                    foreach ($lectura->getLecturaModalidad() as $lecturaModalidad2) {
                        if ($lecturaModalidad->getModalidad()->getDescripcion() == $lecturaModalidad2->getModalidad()->getDescripcion())
                            $counter++;
                    }
                    if ($counter > 1) {
                        $form->addError(
                            new FormError(
                                'Debe especificar las modalidades solo una vez por entrada'
                            )
                        );
                        break;
                    }
                    if (
                        ($lecturaModalidad->getModalidad()->getDescripcion() == 'Fondo'
                            or $lecturaModalidad->getModalidad()->getDescripcion() == 'Estantería Abierta')
                        and $lecturaModalidad->getModalidadDetalle()->count() == 0
                    ) {
                        $form->addError(
                            new FormError(
                                'Para las modalidades Fondo y Estantería Abierta, debe especificar el detalle'
                            )
                        );
                        break;
                    }
                    foreach ($lecturaModalidad->getModalidadDetalle() as $modalidadDetalle) {
                        if ($modalidadDetalle->getDetalle() == '') {
                            $form->addError(
                                new FormError(
                                    'No debe dejar vacío el detalle en ningún caso'
                                )
                            );
                            break;
                        }
                    }
                }
            } else {
                $form->addError(
                    new FormError(
                        'Debe especificar al menos una modalidad a la cual accederá el usuario en el servicio'
                    )
                );
            }
            #endregion
            if ($form->isValid()) {
                if (!is_null($lectura->getId())) {
                    $oldModalidades = $em->getRepository('LecturaBundle:LecturaModalidad')->findBy(
                        array('lectura' => $lectura)
                    );
                    foreach ($oldModalidades as $oldModalidad) {
                        $oldDetalles = $em->getRepository('LecturaBundle:ModalidadDetalle')->findBy(
                            array('lecturaModalidad' => $oldModalidad)
                        );
                        foreach ($oldDetalles as $oldDetalle) {
                            $em->remove($oldDetalle);
                        }
                        $em->remove($oldModalidad);
                    }
                }

                $lectura->setUsuario($usuario);
                $lectura->setEntrada(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $em->persist($lectura);

                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));

                if (!is_null($formLectura['id']) and $formLectura['id'] != "") {
                    $traza->setOperacion('Modificar Entrada');
                    $traza->setObjeto('Usuario');
                    $traza->setObservaciones(
                        'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'lectura')
                        ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                    );
                    $traza->setModulo('Lectura');

                    $this->get('session')->getFlashBag()->add(
                        'info_edit',
                        'Se ha modificado correctamente la entrada del usuario al servicio'
                    );
                } else {
                    $traza->setOperacion('Dar Entrada');
                    $traza->setObjeto('Usuario');
                    $traza->setObservaciones(
                        'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'lectura')
                        ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                    );
                    $traza->setModulo('Lectura');

                    $this->get('session')->getFlashBag()->add(
                        'info_add',
                        'Se ha dado entrada correctamente al usuario'
                    );
                }
                $em->persist($traza);
                $em->flush();

                return $this->redirect(
                    $this->generateUrl(
                        'usuario_detalles',
                        array('id' => $usuario->getId(), 'modulo' => 'lectura', 'page' => 1)
                    )
                );
            }
        } else {
            $this->get('session')->getFlashBag()->add(
                'info_error',
                'El usuario no se encuentra actualmente en la biblioteca; no se le puede dar entrada al servicio.'
            );
        }
        $currentlyInLect = $em->getRepository('LecturaBundle:Lectura')->findCurrentlyInLect($usuario->getId());

        return $this->render(
            'LecturaBundle:Default:detalles.html.twig',
            array(
                'usuario' => $usuario,
                'page' => 1,
                'active' => $classActive,
                'form' => $form->createView(),
                'lectura' => $lectura,
                'currentlyIn' => $currentlyIn,
                'currentlyInLect' => $currentlyInLect,
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('lectFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoEntradasAction();
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function salidaAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
        $lectura = $em->getRepository('LecturaBundle:Lectura')->findBy(
            array('usuario' => $usuario, 'salida' => null)
        );
        try {
            $lectura[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));
        } catch (Exception $e) {
            ;
        }
        $em->persist($lectura[0]);

        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setOperacion('Dar Salida');
        $traza->setObjeto('Usuario');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'lectura')
            ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $traza->setModulo('Lectura');
        $em->persist($traza);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Se ha dado salida correctamente al usuario'
        );

        return $this->redirect(
            $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'modulo' => 'lectura', 'page' => 1)
            )
        );
    }
}
