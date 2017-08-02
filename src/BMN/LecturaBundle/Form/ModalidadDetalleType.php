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

class ModalidadDetalleType extends AbstractType
{
    private $action;
    private $usuario;
    private $id;
    private $lecturaModalidad;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add('detalle', 'text')
            ->add('tipo', 'choice',
                array(
                    'choices' => array('libro' => 'Libro', 'revista' => 'Revista', 'tesis' => 'Tesis', 'disco' => 'Disco'),
                    'expanded' => true
                )
            )
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\LecturaBundle\Entity\ModalidadDetalle',
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