<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\LecturaBundle\Form;


use BMN\LecturaBundle\Entity\ModalidadDetalle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LecturaModalidadType extends AbstractType
{
    private $action;
    private $id;
    private $modalidades;
    private $modalidadDetalles;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add(
                'modalidad',
                'choice',
                array(
                    'choice_list' => $this->modalidades,
                    'expanded' => false,
                    'mapped' => true,
                    'required' => true
                )
            )
            ->add(
                'modalidadDetalle',
                'collection',
                array(
                    'type' => new ModalidadDetalleType(),
                    'required' => false,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype_name' => '__modalidadDetalle__'
                )
            )
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\LecturaBundle\Entity\LecturaModalidad',
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
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $modalidades
     */
    public function setModalidades($modalidades)
    {
        $this->modalidades = $modalidades;
    }

    /**
     * @param mixed $modalidadDetalle
     */
    public function setModalidadDetalle($modalidadDetalle)
    {
        $this->modalidadDetalle = $modalidadDetalle;
    }

    /**
     * @param mixed $modalidadDetalles
     */
    public function setModalidadDetalles($modalidadDetalles)
    {
        $this->modalidadDetalles = $modalidadDetalles;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formLecturaModalidad';
    }
}