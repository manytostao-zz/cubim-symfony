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

class EventType extends AbstractType
{
    private $action;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false))
            ->add('title', 'text', array('required' => true))
            ->add('description', 'textarea', array('required' => true))
            ->add('allDay')
            ->add('calendarView', 'hidden', array('required' => false, 'mapped' => false))
            ->add('currentDate', 'hidden', array('required' => false, 'mapped' => false))
            ->add(
                'start',
                'datetime',
                array(
                    'mapped' => true,
                    'widget' => 'single_text',
//                    'format' => 'd/m/Y H:i:s',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add(
                'end',
                'datetime',
                array(
                    'mapped' => true,
                    'widget' => 'single_text',
//                    'format' => 'd/m/Y H:i:s',
                    'required' => false,
                    'attr' => array('style' => 'width:95%'),
                    'invalid_message' => 'El valor de este campo no es válido.'
                )
            )
            ->add('url')
            ->add('color');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\OtrosBundle\Entity\Event'
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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formEvent';
    }
}