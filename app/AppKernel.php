<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new BMN\UsuarioBundle\UsuarioBundle(),
            new BMN\NomencladorBundle\NomencladorBundle(),
            new BMN\OtrosBundle\OtrosBundle(),
            new BMN\AppUserBundle\AppUserBundle(),
            new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),
            new BMN\RecepcionBundle\RecepcionBundle(),
            new BMN\ReferenciaBundle\ReferenciaBundle(),
            new BMN\ReportesBundle\ReportesBundle(),
            new BMN\NavegacionBundle\NavegacionBundle(),
            new BMN\BibliografiaBundle\BibliografiaBundle(),
            new BMN\DSIBundle\DSIBundle(),
            new BMN\LecturaBundle\LecturaBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
