<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\UsuarioBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioServicioType extends AbstractType
{
    private $action;
    private $servicios;
    private $defaultId;
    private $defaultChapilla;
    private $defaultServicio;
    private $defaultDocumento;
    private $defaultObservaciones;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->defaultId))
            ->add('servicio', 'choice', array('choice_list' => $this->servicios, 'data' => $this->defaultServicio))
            ->add(
                'chapilla',
                'number',
                array(
                    'mapped' => false,
                    'required' => false,
                    'data' => $this->defaultChapilla,
                    'invalid_message' => 'La chapilla debe ser un número entero.'
                )
            )
            ->add(
                'documento',
                'textarea',
                array('mapped' => false, 'required' => false, 'data' => $this->defaultDocumento)
            )
            ->add(
                'observaciones',
                'textarea',
                array('mapped' => false, 'required' => false, 'data' => $this->defaultObservaciones)
            )
            ->add('usuario', 'hidden')
            ->add('Continuar', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\UsuarioBundle\Entity\UsuarioServicio'
            )
        );
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param mixed $servicios
     */
    public function setServicios($servicios)
    {
        $this->servicios = $servicios;
    }

    /**
     * @param mixed $defaultId
     */
    public function setDefaultId($defaultId)
    {
        $this->defaultId = $defaultId;
    }

    /**
     * @param mixed $defaultChapilla
     */
    public function setDefaultChapilla($defaultChapilla)
    {
        $this->defaultChapilla = $defaultChapilla;
    }

    /**
     * @param mixed $defaultServicio
     */
    public function setDefaultServicio($defaultServicio)
    {
        $this->defaultServicio = $defaultServicio;
    }

    /**
     * @param mixed $defaultDocumento
     */
    public function setDefaultDocumento($defaultDocumento)
    {
        $this->defaultDocumento = $defaultDocumento;
    }

    /**
     * @param mixed $defaultObservaciones
     */
    public function setDefaultObservaciones($defaultObservaciones)
    {
        $this->defaultObservaciones = $defaultObservaciones;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formRecepcion';
    }
}