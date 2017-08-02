<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 30/04/2015
 * Time: 12:21
 */

namespace BMN;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ExceptionListener
{
    protected $templating;
    protected $kernel;

    public function __construct(EngineInterface $templating, $kernel)
    {
        $this->templating = $templating;
        $this->kernel = $kernel;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // provide the better way to display a enhanced error page only in prod environment, if you want
        if ('prod' == $this->kernel->getEnvironment()) {
            // exception object
            $exception = $event->getException();
            // new Response object
            $response = new Response();
            // HttpExceptionInterface is a special type of exception
            // that holds status code and header details
            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
            switch ($response->getStatusCode()) {
                case 400:
                    $status_text = "URL inválida. Hay un problema con la dirección\ndel recurso al que está intentando acceder.";
                    break;
                case 401:
                    $status_text = "No autorizado. Usted no tiene privilegios para\noperar con esta parte de la aplicación.\nIntente acceder con otra cuenta.";
                    break;
                case 403:
                    $status_text = "Acceso prohibido. Usted no tiene privilegios para\noperar con esta parte de la aplicación.\nIntente acceder con otra cuenta.";
                    break;
                case 404:
                    $status_text = "Recurso no encontrado. No es posible encontrar lo que busca.\nPuede que haya intentado acceder a la aplicación con una URL inválida.";
                    break;
                case 500:
                    $status_text = "Error de procesamiento interno. La aplicación parece\nhaber procesado incorrectamente los datos.";
                    break;
            }
            // set response content
            $response->setContent(
            // create you custom template
                $this->templating->render(
                    '::error.html.twig',
                    array(
                        'status_text' => $status_text,
                        'status_code' => $response->getStatusCode(),
                        'exception' => $exception
                    )
                )
            );
            // set the new $response object to the $event
            $event->setResponse($response);
        }
    }
}