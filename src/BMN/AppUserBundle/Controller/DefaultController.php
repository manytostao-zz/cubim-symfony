<?php

namespace BMN\AppUserBundle\Controller;

use BMN\AppUserBundle\Entity\AppUser;
use BMN\AppUserBundle\Entity\UserImage;
use BMN\AppUserBundle\Form\AppUserType;
use BMN\AppUserBundle\Form\UserImageType;
use BMN\CUBiMController;
use BMN\OtrosBundle\Entity\Traza;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends CUBiMController
{
    private $currentUserRoles;

    public function loginAction()
    {
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $error = $peticion->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $sesion->get(SecurityContext::AUTHENTICATION_ERROR)
        );
        $navegador = false;
        if (substr_count($peticion->server->get('HTTP_USER_AGENT'), "Firefox") > 0) {
            $navegador = true;
        }

        return $this->render(
            'AppUserBundle:Default:logincc.html.twig',
            array(
                'last_username' => $sesion->get(SecurityContext::LAST_USERNAME),
                'error' => $error,
                'navegador' => $navegador
            )
        );
    }

    public function listAction()
    {
        if ($this->get('security.context')->getToken()->getUser()->isAccountNonLocked()) {
            $classActive = array('sup' => 'AppUsers');
            $peticion = $this->getRequest();
            $sesion = $peticion->getSession();

            return $this->render(
                'AppUserBundle:Default:lista.html.twig',
                array(
                    'active' => $classActive,
                )
            );
        } else {
            return $this->redirect($this->getRequest()->getRequestUri());
        }
    }

    public function getAppUsersListAction()
    {
        $request = $this->getRequest();
        $columnas = array('id', 'nombre', 'apellidos', 'username', 'roles', 'activo');

        return $this->appUsersList($request, $columnas);
    }

    public function createAction()
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = new AppUser();

        $usuarioType = new AppUserType();
        $usuarioType->setTipoForm('add');
        $usuarioType->setAction($this->generateUrl('appuser_save'));
        $roles = $em->getRepository('AppUserBundle:Role')->findAll();
        $usuarioType->setRoles(new ChoiceList($roles, $roles));
        $formUsua = $this->createForm($usuarioType, $usuario);

        $userImage = new UserImage();
        $userImageType = new UserImageType();
        $userImageType->setAction($this->generateUrl('appuser_img'));
        $userImageType->setUserId($usuario->getId());

        $formImg = $this->createForm($userImageType, $userImage);

        return $this->render(
            'AppUserBundle:Default:extra_edit.html.twig',
            array(
                'formUsua' => $formUsua->createView(),
                'tipoForm' => 'add',
                'formImg' => $formImg->createView(),
            )
        );
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('AppUserBundle:AppUser')->find($id);

        $usuarioType = new AppUserType();
        $usuarioType->setId($id);
        $usuarioType->setTipoForm('edit');
        $usuarioType->setAction($this->generateUrl('appuser_save'));
        $roles = $em->getRepository('AppUserBundle:Role')->findAll();
        $usuarioType->setRoles(new ChoiceList($roles, $roles));

        $formUsua = $this->createForm($usuarioType, $usuario);

//        $avatar = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:UserImage')->findByAppUser($id);
//        if (count($avatar) > 0) {
//            $userImage = $avatar[0];
//        } else {
//            $userImage = new UserImage();
//        }
//        $userImageType = new UserImageType();
//        $userImageType->setAction($this->generateUrl('appuser_img'));
//        $userImageType->setUserId($usuario->getId());
//        $userImageType->setUrl('appuser_edit');
//
//        $formImg = $this->createForm($userImageType, $userImage);

        return $this->render(
            'AppUserBundle:Default:extra_edit.html.twig',
            array(
                'formUsua' => $formUsua->createView(),
                'tipoForm' => 'edit',
//                'formImg' => $formImg->createView(),
//                'userImage' => $userImage,
            )
        );
    }

    public function detailsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('AppUserBundle:AppUser')->find($id);
        $appUser = $usuario;
        $trazas = $em->getRepository('OtrosBundle:Traza')->findTrazasFiltros(
            array(
                'appUser' => $usuario->getNombre() . ' ' . $usuario->getApellidos(),
                'operacion' => null,
                'objeto' => null,
                'fechaDesde' => null,
                'fechaHasta' => null
            )
        );
        $avatar = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:UserImage')->findByAppUser($id);
        if (count($avatar) > 0) {
            $userImage = $avatar[0];
        } else {
            $userImage = new UserImage();
        }
        $userImageType = new UserImageType();
        $userImageType->setAction($this->generateUrl('appuser_img'));
        $userImageType->setUserId($usuario->getId());
        $userImageType->setUrl('appuser_details');

        if (!$this->get('security.context')->isGranted('ROLE_ADMINISTRACIÓN') and
            !$this->get('security.context')->isGranted('ROLE_SUPER_ADMINISTRACIÓN')
        ) {
            $usuario = null;
        }
        $formImg = $this->createForm($userImageType, $userImage);

        return $this->render(
            'AppUserBundle:Default:extra_profile.html.twig',
            array(
                'trazas' => $trazas,
                'formImg' => $formImg->createView(),
                'usuario' => $usuario,
                'userImage' => $userImage,
                'appUser' => $appUser
            )
        );
    }

//    /**
//     * @Template()
//     */
//    public function imgUploadAction()
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $form = $this->getRequest()->get('formUserImg');
//        $avatar = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:UserImage')->findByAppUser(
//            $form['userId']
//        );
//        if (count($avatar) > 0) {
//            $userImage = $avatar[0];
//            $em->remove($userImage);
//            $em->flush();
//        }
//
//        $userImage = new UserImage();
//
//        $userImageType = new UserImageType();
//        $userImageType->setAction($this->generateUrl('appuser_img'));
//
//        $formImg = $this->createForm($userImageType, $userImage);
//
//        $formImg->handleRequest($this->getRequest());
//
//        if ($formImg->isValid()) {
//            $appUser = $em->getRepository('AppUserBundle:AppUser')->find($form['userId']);
//            $userImage->preUpload();
//            $userImage->upload();
//            $userImage->setAppUser($appUser);
//            $em->persist($userImage);
//            $em->flush();
//
//            $this->get('session')->getFlashBag()->add(
//                'info_add',
//                'Foto asociada exitosamente'
//            );
//
//            $sesion = $this->getRequest()->getSession();
//
//            $avatar = $em->getRepository('AppUserBundle:UserImage')->findByAppUser(
//                $this->get('security.context')->getToken()->getUser()->getId()
//            );
//
//            $sesion->set(
//                'avatar',
//                $avatar
//            );
//            $this->getRequest()->setSession($sesion);
//
//            $token = new UsernamePasswordToken(
//                $appUser,
//                null,
//                'main',
//                $appUser->getRoles()
//            );
//            $this->container->get('security.context')->setToken($token);
//
//            return $this->redirect($this->generateUrl($form['url'], array('id' => $form['userId'])));
//
//        }
//
//        $usuario = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')->find(
//            $form['userId']
//        );
//
//        $usuarioType = new AppUserType();
//        $usuarioType->setId($usuario->getId());
//        $usuarioType->setTipoForm('edit');
//        $usuarioType->setAction($this->generateUrl('appuser_save'));
//
//        $formUsua = $this->createForm($usuarioType, $usuario);
//
//
//        if ($form['url'] == 'appuser_details') {
//            $usuario = $em->getRepository('AppUserBundle:AppUser')->find($form['userId']);
//            $trazas = $em->getRepository('OtrosBundle:Traza')->findTrazasFiltros(
//                array(
//                    'appUser' => $usuario->getNombre() . ' ' . $usuario->getApellidos(),
//                    'operacion' => null,
//                    'objeto' => null,
//                    'fechaDesde' => null,
//                    'fechaHasta' => null
//                )
//            );
//
//            return $this->render(
//                'AppUserBundle:Default:extra_profile.html.twig',
//                array('trazas' => $trazas, 'formImg' => $formImg->createView())
//            );
//        }
//
//        return $this->render(
//            'AppUserBundle:Default:extra_edit.html.twig',
//            array(
//                'formUsua' => $formUsua->createView(),
//                'tipoForm' => 'edit',
//                'formImg' => $formImg->createView(),
//                'userImage' => $userImage
//            )
//        );
//
//    }
//
//    public function imgRemoveAction($id, $userId)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $avatar = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:UserImage')->find($id);
//        if (!is_null($avatar)) {
//            $em->remove($avatar);
//            $em->flush();
//            $this->get('session')->getFlashBag()->add(
//                'info_add',
//                'Foto eliminada exitosamente'
//            );
//        }
//
//        $sesion = $this->getRequest()->getSession();
//
//        $avatar = $em->getRepository('AppUserBundle:UserImage')->findByAppUser(
//            $this->get('security.context')->getToken()->getUser()->getId()
//        );
//
//        $sesion->set(
//            'avatar',
//            $avatar
//        );
//        $this->getRequest()->setSession($sesion);
//
//        return $this->redirect($this->generateUrl('appuser_edit', array('id' => $userId)));
//    }

    public function editPassAction(
        $id
    ) {
        $usuario = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')->find($id);

        $usuarioType = new AppUserType();
        $usuarioType->setId($id);
        $usuarioType->setTipoForm('reset');
        $usuarioType->setAction($this->generateUrl('appuser_save'));
        $formUsua = $this->createForm($usuarioType, $usuario);


        return $this->render(
            'AppUserBundle:Default:create.html.twig',
            array('formUsua' => $formUsua->createView(), 'tipoForm' => 'reset')
        );
    }

    public function saveAction()
    {
        $em = $this->getDoctrine()->getManager();
        $peticion = $this->getRequest();
        $form = $peticion->get('formUsua');
        if ($form['tipoForm'] == 'add' or $form['tipoForm'] == 'addAsocClie') {
            $usuario = new AppUser();
        } elseif ($form['tipoForm'] == 'edit' or $form['tipoForm'] == 'reset' or $form['tipoForm'] == 'editAsocClie') {
            $usuario = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:AppUser')->find(
                $form['id']
            );
        }

        $id = $form['id'];
        $usuarioType = new AppUserType();
        $usuarioType->setAction($this->generateUrl('appuser_save'));
        $usuarioType->setTipoForm($form['tipoForm']);
        $roles = $em->getRepository('AppUserBundle:Role')->findAll();
        $usuarioType->setRoles(new ChoiceList($roles, $roles));

        /*Copio los datos previos*/
        $prevUsua = new AppUser();
        $prevUsua->setId($usuario->getId());
        $prevUsua->setNombre($usuario->getNombre());
        $prevUsua->setApellidos($usuario->getApellidos());
        $prevUsua->setPassword($usuario->getPassword());
        $prevUsua->setSalt($usuario->getSalt());
        $prevUsua->setUsername($usuario->getUsername());
        $prevUsua->setFechaAlta($usuario->getFechaAlta());
        $prevUsua->setRoles($usuario->getRoles());

        $formUsua = $this->createForm($usuarioType, $usuario);

        $formUsua->handleRequest($peticion);
        if ($formUsua->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form['tipoForm'] == 'add' or $form['tipoForm'] == 'reset' or $form['tipoForm'] == 'edit') {
                if ($form['tipoForm'] == 'reset' or $form['tipoForm'] == 'edit') {

                    $usuario->setNombre($prevUsua->getNombre());
                    $usuario->setApellidos($prevUsua->getApellidos());
                    $usuario->setUsername($prevUsua->getUsername());
                    $usuario->setFechaAlta($prevUsua->getFechaAlta());
                }
                $encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
                $usuario->setSalt(md5(time()));
                $passwordCodificado = $encoder->encodePassword(
                    $usuario->getPassword(),
                    $usuario->getSalt()
                );
                $usuario->setPassword($passwordCodificado);

            } elseif ($form['tipoForm'] == 'edit') {
                $usuario->setPassword($prevUsua->getPassword());
            }
            $em->persist($usuario);

            $traza = new Traza();
            if ($form['tipoForm'] == 'add') {
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('today', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Administración');
                $traza->setOperacion('Adicionar');
                $traza->setObjeto('Usuario de la aplicación');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
                );
                $em->persist($traza);
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Usuario creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Administración');
                $traza->setOperacion('Editar');
                $traza->setObjeto('Usuario de la aplicación');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
                );
                $em->persist($traza);
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Usuario actualizado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'reset') {
                $traza->setAppUser(
                    $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                        'security.context'
                    )->getToken()->getUser()->getApellidos()
                );
                $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
                $traza->setModulo('Administración');
                $traza->setOperacion('Actualizar');
                $traza->setObjeto('Contraseña');
                $traza->setObservaciones(
                    'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
                );
                $em->persist($traza);
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Contraseña actualizada exitosamente'
                );
            }

            $em->flush();

            //Refrescar usuario logueado
            if ($this->get('security.context')->getToken()->getUser()->getId() == $usuario->getId()) {
                $token = new UsernamePasswordToken(
                    $usuario,
                    null,
                    'main',
                    $usuario->getRoles()
                );
                $this->container->get('security.context')->setToken($token);
            }

            return $this->redirect($this->generateUrl('appuser_list'));

        }
        //Refrescar usuario logueado
        if ($this->get('security.context')->getToken()->getUser()->getId() == $prevUsua->getId()) {
            $token = new UsernamePasswordToken(
                $prevUsua,
                null,
                'main',
                $prevUsua->getRoles()
            );
            $this->container->get('security.context')->setToken($token);
        }
        $avatar = $this->getDoctrine()->getManager()->getRepository('AppUserBundle:UserImage')->findByAppUser(
            $prevUsua->getId()
        );
        if (count($avatar) > 0) {
            $userImage = $avatar[0];
        } else {
            $userImage = new UserImage();
        }
        $userImageType = new UserImageType();
        $userImageType->setAction($this->generateUrl('appuser_img'));
        $userImageType->setUserId($prevUsua->getId());
        $formImg = $this->createForm($userImageType, $userImage);

        return $this->render(
            'AppUserBundle:Default:extra_edit.html.twig',
            array(
                'formUsua' => $formUsua->createView(),
                'tipoForm' => $form['tipoForm'],
                'userImage' => $userImage,
                'formImg' => $formImg->createView(),
            )
        );
    }

    public function deleteAction(
        $id
    ) {
        try {
            $em = $this->getDoctrine()->getManager();
            $usuario = $em->find('AppUserBundle:AppUser', $id);

            if (!$usuario) {
                throw $this->createNotFoundException('No se encontró el Usuario.');
            }
            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $traza->setModulo('Administración');
            $traza->setOperacion('Eliminar');
            $traza->setObjeto('Usuario de la aplicación');
            $traza->setObservaciones(
                'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
            );
            $em->persist($traza);
            $em->remove($usuario);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Usuario eliminado exitosamente'
            );
        } catch (DBALException $e) {
            $this->getDoctrine()->resetManager();
            $em = $this->getDoctrine()->getManager();
            $usuario = $em->find('AppUserBundle:AppUser', $id);
            $usuario->setActivo(false);
            $traza = new Traza();
            $traza->setAppUser(
                $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                    'security.context'
                )->getToken()->getUser()->getApellidos()
            );
            $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
            $traza->setModulo('Administración');
            $traza->setOperacion('Desactivar');
            $traza->setObjeto('Usuario de la aplicación');
            $traza->setObservaciones(
                'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
            );
            $em->persist($traza);
            $em->persist($usuario);
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'El usuario se encuentra en uso por registros de la aplicación. En lugar de eliminarse, será desactivado.'
            );
        }

        $em->flush();
            return $this->redirect($this->generateUrl('appuser_list'));

    }

    public function activateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->find('AppUserBundle:AppUser', $id);
        $usuario->setActivo(true);
        $em->persist($usuario);
        $traza = new Traza();
        $traza->setAppUser(
            $this->get('security.context')->getToken()->getUser()->getNombre() . ' ' . $this->get(
                'security.context'
            )->getToken()->getUser()->getApellidos()
        );
        $traza->setFecha(new \DateTime('now', new \DateTimeZone('America/Havana')));
        $traza->setModulo('Administración');
        $traza->setOperacion('Desactivar');
        $traza->setObjeto('Usuario de la aplicación');
        $traza->setObservaciones(
            'Nombre(s) y Apellido(s): ' . $usuario->getNombre() . ' ' . $usuario->getApellidos() . '</a>. '
        );
        $em->persist($traza);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_edit',
            'Usuario activado exitosamente'
        );

        return $this->redirect($this->generateUrl('appuser_list'));
    }

    public function lockAction()
    {
        /*$usuario = $this->get('security.context')->getToken()->getUser();
        $this->get('security.context')->getToken()->setAuthenticated(false);
        $this->get('security.context')->getToken()->eraseCredentials();
        $this->currentUserRoles = $this->get('security.context')->getToken()->getRoles();
        $usuario->setRoles(null);
        $this->get('security.context')->getToken()->setUser($usuario);
//        $this->get('security.context')->getToken()->getUser()->setRoles(null);

        //$usuario->setRoles(null);*/
        //$em = $this->getDoctrine()->getManager();
        $loggedInUser = $this->get('security.context')->getToken()->getUser();
        //$loggedInUser->
        $this->currentUserRoles = $this->get('security.context')->getToken()->getRoles();
        //$loggedInUser->setRoles(new \Doctrine\Common\Collections\ArrayCollection(array('IS_AUTHENTICATED_ANONYMOUSLY')));
        //$loggedInUser->setAccountNonLocked(false);
        /*$em->persist($loggedInUser);
        $em->flush();*/
        $token = new UsernamePasswordToken(
            $loggedInUser,
            null,
            'main',
            array('ROLE_LOCKED')
        );
        $this->container->get('security.context')->setToken($token);

        return $this->render('AppUserBundle:Default:extra_lock.html.twig', array('roles' => $this->currentUserRoles));
    }
}
