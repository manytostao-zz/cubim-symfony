<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 19/03/15
 * Time: 11:28
 */

namespace BMN;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use FOS\JsRoutingBundle\Controller\Controller;

/**
 * Class NomListener
 * @package BMN
 */
class NomListener extends ContainerAware implements EventSubscriberInterface
{
    /**
     * @param FilterControllerEvent $event
     */
    public function onControllerProject(FilterControllerEvent $event)
    {
        #region Código a ejecutar cuando se lance el evento...
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        if (!($controller[0] instanceof ProfilerController)
            and !($controller[0] instanceof ExceptionController)
            and !($controller[0] instanceof Controller)
        ) {
            if (!is_null($controller[0]->container)) {
                if ($controller[0]->container->get('security.context')->getToken()->getUser() != "anon.") {
                    $em = $controller[0]->container->get('doctrine')->getManager();
                    $roles = $controller[0]->container->get('security.context')->getToken()->getUser()->getRoles();
                    $query1 = null;
                    $noti1 = 0;
                    //Get all nomenclators if the role is right
                    foreach ($roles as $role) {
                        switch ($role->getRole()) {
                            case "ROLE_SUPER_ADMINISTRACIÓN" :
                                if ($noti1 == 0) {
                                    $noti1 = $noti1 + 1;
                                    $query1 = $em->createQuery(
                                        "SELECT n FROM NomencladorBundle:TipoNomenclador n
                                        ORDER BY n.descripcion"
                                    );
                                }
                                break;
                            case "ROLE_ADMINISTRACIÓN" :
                                if ($noti1 == 0) {
                                    $noti1 = $noti1 + 1;
                                    $query1 = $em->createQuery(
                                        "SELECT n FROM NomencladorBundle:TipoNomenclador n
                                        ORDER BY n.descripcion"
                                    );
                                }
                                break;
                        }
                    }

                    $result = array();
                    $query = null;
                    $noti1 = 0;
                    $noti2 = 0;
                    $noti3 = 0;
                    $noti4 = 0;
                    $noti5 = 0;
                    foreach ($roles as $role) {
                        switch ($role->getRole()) {
                            case "ROLE_SUPER_ADMINISTRACIÓN":
                                if ($noti1 == 0) {
                                    $noti1 = $noti1 + 1;

                                    $query = $em->createQuery(
                                        'SELECT u.nombres, u.id
                         FROM RecepcionBundle:Recepcion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today'
                                    );
                                    $query->setParameter('today', new \DateTime('yesterday'), 'date');
                                    $result['recepcion'] = $query->getResult();
                                }
                                if ($noti2 == 0) {
                                    $noti2 = $noti2 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(r)
                         FROM ReferenciaBundle:Referencia r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['referencia']['referencia'] = $queryResult[0][1];
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0
                          AND b.referencia = 1"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['referencia']['bibliografia'] = $queryResult[0][1];
                                }
                                if ($noti3 == 0) {
                                    $noti3 = $noti3 + 1;
                                    $query = $em->createQuery(
                                        "SELECT u.nombres, u.id
                         FROM NavegacionBundle:Navegacion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today"
                                    );
                                    $query->setParameter('today', new \DateTime('yesterday'), 'date');
                                    $result['navegacion'] = $query->getResult();
                                }
                                if ($noti4 == 0) {
                                    $noti4 = $noti4 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0"
                                    );
                                    $result['bibliografia'] = $query->getResult();
                                }
                                if ($noti5 == 0) {
                                    $noti5 = $noti5 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(r)
                         FROM DSIBundle:DSI r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['dsi']['referencia'] = $queryResult[0][1];
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0
                          AND b.dsi = 1"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['dsi']['bibliografia'] = $queryResult[0][1];
                                }
                                break;
                            case "ROLE_RECEPCION":
                                if ($noti1 == 0) {
                                    $noti1 = $noti1 + 1;

                                    $query = $em->createQuery(
                                        'SELECT u.nombres, u.id
                         FROM RecepcionBundle:Recepcion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today'
                                    );
                                    $query->setParameter('today', new \DateTime('yesterday'), 'date');
                                    $result['recepcion'] = $query->getResult();
                                }
                                break;
                            case "ROLE_REFERENCIA":
                                if ($noti2 == 0) {
                                    $noti2 = $noti2 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(r)
                         FROM ReferenciaBundle:Referencia r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['referencia']['referencia'] = $queryResult[0][1];
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0
                          AND b.referencia = 1"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['referencia']['bibliografia'] = $queryResult[0][1];
                                }
                                break;
                            case "ROLE_NAVEGACION":
                                if ($noti3 == 0) {
                                    $noti3 = $noti3 + 1;
                                    $query = $em->createQuery(
                                        "SELECT u.nombres, u.id
                         FROM NavegacionBundle:Navegacion r, UsuarioBundle:Usuario u
                         WHERE r.usuario = u.id
                          AND r.salida IS NULL
                          AND r.entrada < :today"
                                    );
                                    $query->setParameter('today', new \DateTime('yesterday'), 'date');
                                    $result['navegacion'] = $query->getResult();
                                }
                                break;
                            case "ROLE_BIBLIOGRAFIA":
                                if ($noti4 == 0) {
                                    $noti4 = $noti4 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0"
                                    );
                                    $result['bibliografia'] = $query->getResult();
                                }
                                break;
                            case "ROLE_DSI":
                                if ($noti5 == 0) {
                                    $noti5 = $noti5 + 1;
                                    $query = $em->createQuery(
                                        "SELECT COUNT(r)
                         FROM DSIBundle:DSI r
                         WHERE r.respuesta = '' OR r.respuesta IS NULL"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['dsi']['referencia'] = $queryResult[0][1];
                                    $query = $em->createQuery(
                                        "SELECT COUNT(b)
                         FROM BibliografiaBundle:Bibliografia b
                         WHERE (SELECT COUNT(br) FROM BibliografiaBundle:BibliografiaRespuesta br
                                          WHERE br.bibliografia = b.id) = 0
                          AND b.dsi = 1"
                                    );
                                    $queryResult = $query->getResult();
                                    if ($queryResult[0][1] > 0)
                                        $result['dsi']['bibliografia'] = $queryResult[0][1];
                                }
                                break;
                        }
                    }

                    $twig = $controller[0]->container->get('twig');
                    $twig->addGlobal('nomencladores', !is_null($query1) ? $query1->getResult() : $query1);
                    $twig->addGlobal('notificaciones', $result);
                }
            }
        }
        #endregion
    }

    #region Otras funciones de la clase...
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
    }
    #endregion
}