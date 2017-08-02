<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\BibliografiaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BibliografiaRespuestaType extends AbstractType
{
    private $action;
    private $bibliografia;
    private $fuentesInfo;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false))
            ->add('bibliografia', 'hidden', array('mapped' => false, 'data' => $this->bibliografia))
            ->add('descriptores')
            ->add('citasRelevantes')
            ->add('citasPertinentes')
            ->add('citas', 'textarea')
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
            ->add('observaciones', 'textarea', array('required' => false))
            ->add('Guardar', 'submit')
            ->getForm();
    }

    /**
     * @param mixed $bibliografia
     */
    public function setBibliografia($bibliografia)
    {
        $this->bibliografia = $bibliografia;
    }

    /**
     * @param mixed $fuentesInfo
     */
    public function setFuentesInfo($fuentesInfo)
    {
        $this->fuentesInfo = $fuentesInfo;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\BibliografiaBundle\Entity\BibliografiaRespuesta'
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
        return 'formBiblioRespuesta';
    }
}