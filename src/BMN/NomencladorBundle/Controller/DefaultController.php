<?php

namespace BMN\NomencladorBundle\Controller;

use BMN\CUBiMController;
use BMN\NomencladorBundle\Entity\Nomenclador;
use BMN\NomencladorBundle\Form\NomType;
use BMN\UsuarioBundle\Entity\Usuario;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use BMN\OtrosBundle\Entity\Traza;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package BMN\NomencladorBundle\Controller
 */
class DefaultController extends CUBiMController
{
    /**
     * @param $idTipoNom
     * @param null $formAdd
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listaAction($idTipoNom, $formAdd = null)
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $classActive = array('sup' => 'Nomencladores', 'sub' => $idTipoNom);
        $em = $this->getDoctrine()->getManager();
        $currentNom = $this->getDoctrine()->getManager()->getRepository('NomencladorBundle:TipoNomenclador')->findBy(
            array('id' => $idTipoNom)
        );
        $nom = new Nomenclador();
        $nomType = new NomType();
        $nomType->setAction($this->generateUrl('nomenclador_salvar'));
        $nomType->setTiponom($idTipoNom);
        if (is_null($formAdd)) {
            $tiposNom = $em->getRepository('NomencladorBundle:TipoNomenclador')->findAll();
            $nomType->setTiponom(new ChoiceList($tiposNom, $tiposNom, $currentNom));
            $form = $this->createForm($nomType, $nom);
        } else {
            $form = $formAdd;
        }
        $sesion->set('salvar', 1);

        return $this->render(
            'NomencladorBundle:Default:lista.html.twig',
            array(
                'form' => $form->createView(),
                'searchForm' => $this->getSearchForm()->createView(),
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'currentNom' => $currentNom,
                'active' => $classActive
            )
        );
    }

    /**
     * @param $idTipoNom
     * @param $id
     * @param $page
     * @param null $formEdit
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editarAction($idTipoNom, $id, $page, $formEdit = null)
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $classActive = array('sup' => 'Nomencladores', 'sub' => $idTipoNom);

        //Formulario para filtrar
        $filtros['tiponom'] = $idTipoNom;
        if ($sesion->has('filtros')) {
            $sesion_filtros = $sesion->get('filtros');
            if ($sesion_filtros['tiponom'] != $filtros['tiponom']) {
                $sesion->set('filtros', $filtros);
            }
        }

        $formTipoNom = $peticion->get('form');
        if (isset($formTipoNom['tipoForm'])) {
            $formTipoNom = null;
        }
        if (!is_null($formTipoNom)) {
            $filtros['descripcion'] = $formTipoNom['descripcion'];

            $sesion->set('filtros', $filtros);
        }
        $filtros = $sesion->get('filtros');
        $nom = new Nomenclador();
        $formFiltro = $this->createFormBuilder($nom, array('attr' => array('name' => 'tipoPro')))
            ->add(
                'descripcion',
                'text',
                array(
                    'required' => false,
                    'attr' => array('class' => 'form-control input-sm'),
                    'data' => isset($filtros['descripcion']) ? $filtros['descripcion'] : ''
                )
            )
            ->add('tiponom', 'hidden', array('data' => $idTipoNom))
            ->setAction(
                $this->generateUrl('nomenclador_lista', array('idTipoNom' => $idTipoNom))
            )
            ->getForm();

        //Formulario para adicionar
        $formAdd = $this->createFormBuilder($nom, array('attr' => array('name' => 'add')))
            ->add('tipoForm', 'hidden', array('data' => 1, 'mapped' => false))
            ->add('page', 'hidden', array('mapped' => false))
            ->add('descripcion', 'text', array('attr' => array('style' => 'width:95%')))
            ->add('tiponom', 'hidden')
            ->add('Guardar', 'submit', array('attr' => array('class' => 'art-button')))
            ->setAction(
                $this->generateUrl('nomenclador_salvar')
            )
            ->getForm();

        //Formulario para editar
        if (!is_null($id) and is_null($formEdit)) {
            $nom1 = $this->getDoctrine()->getManager()->getRepository('NomencladorBundle:Nomenclador')->find($id);
            $formEdit = $this->createFormBuilder($nom1, array('attr' => array('name' => 'edit')))
                ->add('tipoForm', 'hidden', array('data' => 2, 'mapped' => false))
                ->add('page', 'hidden', array('data' => $page, 'mapped' => false))
                ->add('id', 'hidden', array('mapped' => false, 'data' => $nom1->getId()))
                ->add('descripcion')
                ->add('tiponom', 'hidden')
                ->add('Aplicar', 'submit', array('attr' => array('class' => 'art-button')))
                ->setAction(
                    $this->generateUrl('nomenclador_salvar')
                )
                ->getForm();
        }


        $nomencladores = null;
        //Creando el paginador
        $itemsPerPage = $peticion->get('itemsAmmount');
        if (!$itemsPerPage) {
            if (!$sesion->has('itemsPerPage') || (!$sesion->get('itemsPerPage'))) {
                $sesion->set('itemsPerPage', $this->container->getParameter('paginator.items_per_page'));
            }
        } else {
            if ($itemsPerPage > 0) {
                $sesion->set('itemsPerPage', $itemsPerPage);
            }
        }
        $paginador = $this->get('ideup.simple_paginator');

        $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
        $paginador->setMaxPagerItems(7);


        $peticion->setSession($sesion);

        $em = $this->getDoctrine()->getManager();
        if ($peticion->isMethod('POST') && (!$peticion->request->has('itemsAmmount'))) {
            $nomencladores = $paginador->paginate(
                $em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros($filtros)
            )->getResult();
        } else {
            $nomencladores = $paginador->paginate(
                $em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros(
                    $sesion->has('filtros') ? $sesion->get('filtros') : array('tipoNom' => 1)
                )
            )->getResult();
        }

        $currentNom = $this->getDoctrine()->getManager()->getRepository('NomencladorBundle:TipoNomenclador')->find(
            $idTipoNom
        );

        return $this->render(
            'NomencladorBundle:Default:lista.html.twig',
            array(
                'nomencladores' => $nomencladores,
                'paginador' => $paginador,
                'formFiltro' => $formFiltro->createView(),
                'formAdd' => $formAdd->createView(),
                'formEdit' => $formEdit->createView(),
                'id' => $id,
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'currentNom' => $currentNom,
                'active' => $classActive
            )

        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function salvarAction()
    {
        $peticion = $this->getRequest();
        if ($peticion->hasSession()) {
            $sesion = $peticion->getSession();
            if (!$sesion->isStarted()) {
                $sesion->start();
            }
        }
        $tipoForm = $peticion->get('formNomenclador');

        $em = $this->getDoctrine()->getManager();
        if ($tipoForm['tipoForm'] == 1) {
            $nom = new Nomenclador();
        } else {
            $id = $tipoForm['id'];
            $nom = $em->getRepository('NomencladorBundle:Nomenclador')->find($id);
        }
        $currentNom = $this->getDoctrine()->getManager()->getRepository('NomencladorBundle:TipoNomenclador')->findBy(
            array('id' => $tipoForm['tiponom'])
        );
        $nomType = new NomType();
        $nomType->setAction($this->generateUrl('nomenclador_salvar'));
        $tiposNom = $em->getRepository('NomencladorBundle:TipoNomenclador')->findAll();
        $nomType->setTiponom(new ChoiceList($tiposNom, $tiposNom, $currentNom));
        $form = $this->createForm($nomType, $nom);

        $form->handleRequest($peticion);

        if ($form->isValid()) {
            $em->persist($nom);
            if ($tipoForm['tipoForm'] == 1) {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Se han almacenado correctamente los datos del nomenclador.'
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Se han modificado correctamente los datos del nomenclador.'
                );
            }
            $traza = new Traza();

            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            if ($tipoForm['tipoForm'] == 1) {
                $traza->setOperacion('Adicionar');
            } else {
                $traza->setOperacion('Editar');
            }
            $traza->setObjeto('Nomenclador');
            $traza->setModulo('Administración');
            $traza->setObservaciones(
                'Tipo de Nomenclador: ' . $em->getRepository('NomencladorBundle:TipoNomenclador')->find(
                    $nom->getTiponom()
                )->getDescripcion() . '.<br />
                    Descripción: ' . $nom->getDescripcion() . '. '
            );
            $em->persist($traza);
            $em->flush();


            return $this->redirect(
                $this->generateUrl(
                    'nomenclador_lista',
                    array('idTipoNom' => $nom->getTiponom()->getId())
                )
            );

        } else {
            return $this->listaAction($nom->getTiponom(), $form);
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function eliminarAction(
        $id
    )
    {
        $em = $this->getDoctrine()->getManager();
        $nomenclador = $em->find('NomencladorBundle:Nomenclador:Nomenclador', $id);
        $idTipoNom = $nomenclador->getTipoNom()->getId();

        if (!$nomenclador) {
            $this->createNotFoundException('No se encontró el nomenclador.');
        } else {

            try {
                $em->remove($nomenclador);
                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setOperacion('Eliminar');
                $traza->setObjeto('Nomenclador');
                $traza->setModulo('Administración');
                $traza->setObservaciones(
                    'Tipo de Nomenclador: ' . $em->getRepository('NomencladorBundle:TipoNomenclador')->find(
                        $nomenclador->getTiponom()
                    )->getDescripcion() . '.<br />
                    Descripción: ' . $nomenclador->getDescripcion() . '. '
                );
                $em->persist($traza);

                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Se han eliminado correctamente los datos del nomenclador.'
                );
            } catch (DBALException $e) {
                $this->getDoctrine()->resetManager();
                $em = $this->getDoctrine()->getManager();
                $nomenclador = $em->find('NomencladorBundle:Nomenclador:Nomenclador', $id);
                $nomenclador->setActivo(false);
                $em->persist($nomenclador);
                $traza = new Traza();
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setOperacion('Desactivar');
                $traza->setObjeto('Nomenclador');
                $traza->setModulo('Administración');
                $traza->setObservaciones(
                    'Tipo de Nomenclador: ' . $em->getRepository('NomencladorBundle:TipoNomenclador')->find(
                        $nomenclador->getTiponom()
                    )->getDescripcion() . '.<br />
                    Descripción: ' . $nomenclador->getDescripcion() . '. '
                );
                $em->persist($traza);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'El valor del nomenclador se encuentra en uso por registros de la aplicación. En lugar de eliminarse, será desactivado.'
                );
            }

            return $this->redirect($this->generateUrl('nomenclador_lista', array('idTipoNom' => $idTipoNom)));
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activarAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $nomenclador = $em->find('NomencladorBundle:Nomenclador:Nomenclador', $id);
        $nomenclador->setActivo(true);
        $em->persist($nomenclador);
        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setOperacion('Activar');
        $traza->setObjeto('Nomenclador');
        $traza->setModulo('Administración');
        $traza->setObservaciones(
            'Tipo de Nomenclador: ' . $em->getRepository('NomencladorBundle:TipoNomenclador')->find(
                $nomenclador->getTiponom()
            )->getDescripcion() . '.<br />
                    Descripción: ' . $nomenclador->getDescripcion() . '. '
        );
        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_edit',
            'Valor de nomenclador activado exitosamente.'
        );

        return $this->redirect(
            $this->generateUrl('nomenclador_lista', array('idTipoNom' => $nomenclador->getTipoNom()->getId()))
        );
    }

    /**
     * @return mixed
     */
    public function getSearchForm()
    {
        $usuario = new Usuario();

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
                    'attr' => array('class' => 'editor-field', 'style' => 'width: 100%')
                )
            )
            ->setAction($this->generateUrl('usuario_lista', array('modulo' => 'usuario')))
            ->setMethod('POST')
            ->getForm();

        return $searchForm;
    }

    /**
     * @param $tipoNom
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getNomencladorListadoAction($tipoNom)
    {
        $request = $this->getRequest();

        return $this->nomencladorListAction($request, $tipoNom);
    }

    /**
     * @return Response
     */
    public function nomencladorSelectAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $result = [];
        $result['total_count'] = 0;
        $result['items'] = [];
        $nomencladores = null;
        $params = $request->get('q');
        if (array_key_exists('description', $params)){
            $nomencladores = $em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros(
                array('tiponom' => $params['tipoNomId'], 'descripcion' => $params['description']),
                intval($params['pageCount']),
                intval($params['page']) > 0 ? intval($params['pageCount'] * ($params['page'] - 1)) : null
            );

            $result['total_count'] = count($em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros(
                array('tiponom' => $params['tipoNomId'], 'descripcion' => $params['description'])
            ));
        }
        elseif (array_key_exists('id', $params)){
            $nomencladores = $em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros(
                array('tiponom' => $params['tipoNomId'], 'id' => $params['id']),
                intval($params['pageCount']),
                intval($params['page']) > 0 ? intval($params['pageCount'] * ($params['page'] - 1)) : null
            );

            $result['total_count'] = count($em->getRepository('NomencladorBundle:Nomenclador')->findNomencladoresFiltros(
                array('tiponom' => $params['tipoNomId'], 'id' => $params['id'])
            ));
        }
        for ($i = 0; $i < count($nomencladores); $i++) {
            $result['items'][$i]['id'] = $nomencladores[$i]->getId();
            $result['items'][$i]['text'] = $nomencladores[$i]->getDescripcion();
        }
        $result['incomplete_results'] = false;

        return new Response(json_encode($result));
    }
}
