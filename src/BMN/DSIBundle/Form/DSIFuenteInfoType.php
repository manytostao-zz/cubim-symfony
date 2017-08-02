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

class DSIFuenteInfoType extends AbstractType
{
    private $action;
    private $fuentesInfo;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('dsi_id', 'hidden')
            ->add('nomenclador_id', 'choice', array('choice_list' => $this->fuentesInfo))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\DSIBundle\Entity\DSIFuenteInfo'
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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formNomenclador';
    }
}