<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 23/10/14
 * Time: 15:19
 */

namespace BMN\AppUserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserImageType extends AbstractType
{
    private $action;

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    private $userId;

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    private $url;

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('userId', 'hidden', array('mapped' => false, 'data' => $this->userId))
            ->setAction($this->action)
            ->add('name')
            ->add('file', 'file')
            ->add('url', 'hidden', array('mapped' => false, 'data' => $this->url))
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
                'data_class' => 'BMN\AppUserBundle\Entity\UserImage',
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formUserImg';
    }
}