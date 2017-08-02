<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:55
 */

namespace BMN\AppUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppUserType extends AbstractType
{
    private $id;
    private $dataClie;
    private $action;
    private $tipoForm;
    private $roles;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formUsua')
            ->setAction($this->action)
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add('nombre', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('apellidos', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('username', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add(
                'password',
                'repeated',
                array(
                    'type' => 'password',
                    'invalid_message' => 'Las dos contraseñas deben coincidir',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('roles', 'choice', array('choice_list' => $this->roles, 'multiple' => true, 'expanded' => true))
            ->getForm();
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\AppUserBundle\Entity\AppUser'
            )
        );
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $clientesChoices
     */
    public function setClientesChoices($clientesChoices)
    {
        $this->clientesChoices = $clientesChoices;
    }

    /**
     * @param $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $tipoForm
     */
    public function setTipoForm($tipoForm)
    {
        $this->tipoForm = $tipoForm;
    }

    /**
     * @param mixed $dataClie
     */
    public function setDataClie($dataClie)
    {
        $this->dataClie = $dataClie;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formUsua';
    }
}
