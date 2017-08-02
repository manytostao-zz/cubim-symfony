<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\OtrosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TrazaType extends AbstractType
{
    private $action;
    private $appUsersOptions;
    private $fechaOptions;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('modulo')
            ->add('operacion')
            ->add('objeto')
            ->add('appUser')
            ->add(
                'fechaDesde',
                'birthday',
                array(
                    'mapped' => false,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'label' => 'Fecha de Desde',
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add(
                'fechaHasta',
                'birthday',
                array(
                    'mapped' => false,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'required' => false,
                    'label' => 'Fecha de Hasta',
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add(
                'fechaOper',
                'hidden',
                array('mapped' => false, 'data' => '>=')
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\OtrosBundle\Entity\Traza'
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
     * @param mixed $appUsersOptions
     */
    public function setAppUsersOptions($appUsersOptions)
    {
        $this->appUsersOptions = $appUsersOptions;
    }

    /**
     * @param mixed $fechaOptions
     */
    public function setFechaOptions($fechaOptions)
    {
        $this->fechaOptions = $fechaOptions;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formTraza';
    }
}