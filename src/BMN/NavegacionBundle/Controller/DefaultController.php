<?php

namespace BMN\NavegacionBundle\Controller;

use BMN\CUBiMController;
use BMN\NavegacionBundle\Entity\Navegacion;
use BMN\NavegacionBundle\Form\NavegacionType;
use BMN\NomencladorBundle\Entity\Nomenclador;
use BMN\NomencladorBundle\Form\NomencladorType;
use BMN\OtrosBundle\Entity\Traza;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

/**
 * Class DefaultController
 * @package BMN\NavegacionBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function entradaAction()
    {
        $classActive = array('sup' => 'recepcion', 'sub' => 'Lista');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $formNavegacion = $peticion->get('formNavegacion');
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($formNavegacion['usuario']);
        $currentlyIn = $em->getRepository('RecepcionBundle:Recepcion')->findCurrentlyIn($usuario->getId());
        $navegacionType = new NavegacionType();

        $navegacionType->setAction($this->generateUrl('navegacion_entrada'));
        if (!is_null($formNavegacion['id']) and $formNavegacion['id'] != "") {
            $navegacion = $em->getRepository('NavegacionBundle:Navegacion')->find($formNavegacion['id']);
            $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
            $navegacion->restartFuentesInfo();
        } else {
            $navegacion = new Navegacion();
            $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
            $currentlyInUsePc = $em->getRepository('NavegacionBundle:Navegacion')->findBy(array('salida' => null));
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

        if (count($currentlyIn) > 0) {
            $form->handleRequest($peticion);

            if ($form->isValid()) {
                $fuentesInfoCorrected = new ArrayCollection();
                foreach ($navegacion->getFuentesInfo() as $fuenteInfo) {
                    if ($fuenteInfo instanceof Nomenclador) {
                        $fuentesInfoCorrected->add($fuenteInfo->getId());
                    }
                }
                $navegacion->getFuentesInfo()->clear();
                foreach ($fuentesInfoCorrected as $fuenteInfo) {
                    $navegacion->getFuentesInfo()->add($fuenteInfo);
                }
                $navegacion->setUsuario($usuario);
                $navegacion->setEntrada(new \DateTime('now', new \DateTimeZone('America/Havana')));
                if ($navegacion->getCorreo()) {
                    $navegacion->setNecesidad(null);
                    $navegacion->setObservaciones(null);
                    $navegacion->restartFuentesInfo();
                }
                $em->persist($navegacion);

                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));

                if (!is_null($formNavegacion['id']) and $formNavegacion['id'] != "") {
                    $traza->setOperacion('Modificar Entrada');
                    $traza->setObjeto('Usuario');
                    $traza->setObservaciones(
                        'Nombre(s) y Apellido(s): <a href="' . $this->generateUrl(
                            'usuario_detalles',
                            array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'navegacion')
                        ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                    );
                    $traza->setModulo('Navegación');

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
                            array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'navegacion')
                        ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
                    );
                    $traza->setModulo('Navegación');

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
                        array('id' => $usuario->getId(), 'modulo' => 'navegacion', 'page' => 1)
                    )
                );
            }
        } else {
            $this->get('session')->getFlashBag()->add(
                'info_error',
                'El usuario no se encuentra actualmente en la biblioteca; no se le puede dar entrada al servicio.'
            );
        }
        $currentlyInNav = $em->getRepository('NavegacionBundle:Navegacion')->findCurrentlyInNav($usuario->getId());

        return $this->render(
            'NavegacionBundle:Default:detalles.html.twig',
            array(
                'usuario' => $usuario,
                'page' => 1,
                'active' => $classActive,
                'form' => $form->createView(),
                'navegacion' => $navegacion,
                'currentlyIn' => $currentlyIn,
                'currentlyInNav' => $currentlyInNav
            )
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function salidaAction($id)
    {
        $classActive = array('sup' => 'navegacion', 'sub' => 'Lista');
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('UsuarioBundle:Usuario')->find($id);
        $navegacion = $em->getRepository('NavegacionBundle:Navegacion')->findBy(
            array('usuario' => $usuario, 'salida' => null)
        );
        try {
            $navegacion[0]->setSalida(new \DateTime('now', new \DateTimeZone('America/Havana')));
        } catch (Exception $e) {
            ;
        }
        $em->persist($navegacion[0]);

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
                array('id' => $usuario->getId(), 'page' => 1, 'modulo' => 'navegacion')
            ) . '">' . $usuario->getNombres() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $traza->setModulo('Navegación');
        $em->persist($traza);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Se ha dado salida correctamente al usuario'
        );

        return $this->redirect(
            $this->generateUrl(
                'usuario_detalles',
                array('id' => $usuario->getId(), 'modulo' => 'navegacion', 'page' => 1)
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listadoEntradasAction()
    {
        $classActive = array('sup' => 'navegacion', 'sub' => 'entradas');
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $submit = $peticion->get('form');

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

        if (is_null($submit)) {
            $submit = $sesion->get('naveFilters');
        }

        $fechaHasta = new \DateTime('today', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and array_key_exists('fechaHasta', $submit) and $submit['fechaHasta'] != '') {
            $fecha = explode('/', $submit['fechaHasta']);
            $fechaHasta->setDate($fecha[2], $fecha[1], $fecha[0]);
        }

        $fechaDesde = new \DateTime('today', new \DateTimeZone('America/Havana'));
        if (!is_null($submit) and array_key_exists('fechaDesde', $submit) and $submit['fechaDesde'] != '') {
            $fecha = explode('/', $submit['fechaDesde']);
            $fechaDesde->setDate($fecha[2], $fecha[1], $fecha[0]);
        }

        $fuentesInfo = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(15);
        $fuentesInfoKeys = array();
        foreach ($fuentesInfo as $fuenteInfo) {
            $fuentesInfoKeys[$fuenteInfo->getDescripcion()] = $fuenteInfo->getDescripcion();
        }
        $pcs = $em->getRepository('NomencladorBundle:Nomenclador')->findByTiponom(17);
        $pcsKeys = array();
        foreach ($pcs as $pc) {
            $pcsKeys[$pc->getDescripcion()] = $pc->getDescripcion();
        }
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('navegacion_listado_entradas'))
            ->add(
                'usuario',
                'text',
                array(
                    'mapped' => false,
                    'data' => (!is_null($submit) and array_key_exists('usuario', $submit)) ? $submit['usuario'] : ''
                )
            )
            ->add(
                'correo',
                'checkbox',
                array(
                    'required' => false,
                    'data' => (!is_null($submit) and array_key_exists(
                            'correo',
                            $submit
                        )) ? $submit['correo'] == 1 : false
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
                        )) ? $submit['fuentesInfo'] : ''
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
                    'data' => (!is_null($submit) and array_key_exists('pc', $submit)) ? $submit['pc'] : ''
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
                    )
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
                    )
                )
            );
        $sesion->set('naveFilters', $submit);
        $peticion->setSession($sesion);

        return $this->render(
            'NavegacionBundle:Default:listadoEntradas.html.twig',
            array(
                'active' => $classActive,
                'form' => $form->getForm()->createView(),
                'submit' => $submit
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEntradasAction()
    {
        $request = $this->getRequest();
        $sesion = $this->getRequest()->getSession();
        $filtros = $sesion->get('naveFilters') != null ? $sesion->get('naveFilters') : array(
            "fechaDesde" => null,
            "fechaHasta" => null
        );
        $from = $request->get('from');
        $id = $request->get('id');
        if ($from == 'userDetails') {
            $columnas = array(
                'fecha',
                'pc',
                'correo',
                'fuentesInfo',
                'necesidad',
                'observaciones',
                'entrada',
                'salida'
            );
            $filtros['usuario_id'] = $id;
        } else {
            $columnas = array(
                'fecha',
                'pc',
                'usuario',
                'correo',
                'fuentesInfo',
                'necesidad',
                'observaciones',
                'entrada',
                'salida'
            );
        }

        return $this->getNavegacionEntradas($request, $filtros, $columnas);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function limpiarFiltrosAction()
    {
        $sesion = $this->getRequest()->getSession();

        $sesion->remove('naveFilters');

        $this->getRequest()->setSession($sesion);

        return $this->listadoEntradasAction();
    }
}
