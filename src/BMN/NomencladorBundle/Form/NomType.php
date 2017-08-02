<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\NomencladorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NomType extends AbstractType
{
    private $action;
    private $tiponom;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('tipoForm', 'hidden', array('data' => 1, 'mapped' => false))
            ->add('id', 'hidden', array('mapped' => false))
            ->add('descripcion', 'text', array('required' => true, 'attr' => array('style' => 'width:95%')))
            ->add('activo', 'checkbox', array('required' => false))
            ->add('tiponom', 'choice', array('choice_list' => $this->tiponom, 'empty_value' => false))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\NomencladorBundle\Entity\Nomenclador'
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
     * @param $tiponom
     */
    public function setTiponom($tiponom)
    {
        $this->tiponom = $tiponom;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formNomenclador';
    }
}