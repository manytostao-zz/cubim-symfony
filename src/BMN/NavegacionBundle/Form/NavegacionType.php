<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 31/10/14
 * Time: 8:28
 */

namespace BMN\NavegacionBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NavegacionType extends AbstractType
{
    private $action;
    private $pcs;
    private $usuario;
    private $id;
    private $fuentesInfo;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add('usuario', 'hidden', array('mapped' => false, 'data' => $this->usuario))
            ->add('correo',  'checkbox', array('required' => false))
            ->add('necesidad',  'textarea', array('required' => false))
            ->add('observaciones',  'textarea', array('required' => false))
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
                'pc',
                'choice',
                array(
                    'choice_list' => $this->pcs,
                    'mapped' => true,
                    'required' => true
                )
            )
            ->add('Guardar', 'submit')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\NavegacionBundle\Entity\Navegacion'
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
     * @param mixed $pcs
     */
    public function setPcs($pcs)
    {
        $this->pcs = $pcs;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @param mixed $fuentesInfo
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
        return 'formNavegacion';
    }
}