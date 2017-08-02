<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\DSIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DSIType extends AbstractType
{
    private $action;
    private $viaSolicitud;
    private $fuentesInfo;
    private $tipoRespuesta;
    private $usuario;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false))
            ->add('usuario', 'hidden', array('mapped' => false, 'data' => $this->usuario))
            ->add('viaSolicitud', 'choice', array('choice_list' => $this->viaSolicitud))
            ->add(
                'fuentesInfo',
                'collection',
                array(
                    'type' => $this->fuentesInfo,
                    'required' => false,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true
                )
            )
            ->add(
                'tipoRespuesta',
                'choice',
                array(
                    'choice_list' => $this->tipoRespuesta,
                    'expanded' => true,
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false
                )
            )
            ->add('pregunta', 'textarea', array('required' => true))
            ->add('respuesta', 'textarea', array('required' => false))
            ->add('desiderata', 'checkbox', array('required' => false))
            ->add('name', 'text', array('required' => false))
            ->add('file', 'file', array('required' => false))
            ->add('Guardar', 'submit')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\DSIBundle\Entity\DSI'
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
     * @param mixed $fuenteInfo
     */
    public function setFuentesInfo($fuentesInfo)
    {
        $this->fuentesInfo = $fuentesInfo;
    }

    /**
     * @param mixed $tipoRespuesta
     */
    public function setTipoRespuesta($tipoRespuesta)
    {
        $this->tipoRespuesta = $tipoRespuesta;
    }

    /**
     * @param mixed $viaSolicitud
     */
    public function setViaSolicitud($viaSolicitud)
    {
        $this->viaSolicitud = $viaSolicitud;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formDSI';
    }
}