<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\LecturaBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class LecturaType
 * @package BMN\LecturaBundle\Form
 */
class LecturaType extends AbstractType
{
    /**
     * @var
     */
    private $action;
    /**
     * @var
     */
    private $usuario;
    /**
     * @var
     */
    private $id;
    /**
     * @var
     */
    private $lecturaModalidad;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add('usuario', 'hidden', array('mapped' => false, 'data' => $this->usuario))
            ->add('observaciones', 'textarea', array('required' => false))
            ->add(
                'lecturaModalidad',
                'collection',
                array(
                    'type' => $this->lecturaModalidad,
                    'required' => false,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype_name' => '__lecturaModalidad__'
                )
            )
            ->add('Guardar', 'submit')
            ->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\LecturaBundle\Entity\Lectura',
            )
        );
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @param mixed $lecturaModalidad
     */
    public function setLecturaModalidad($lecturaModalidad)
    {
        $this->lecturaModalidad = $lecturaModalidad;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formLectura';
    }
}