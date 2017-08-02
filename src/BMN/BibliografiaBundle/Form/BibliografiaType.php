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

class BibliografiaType extends AbstractType
{
    private $id;
    private $action;
    private $usuario;
    private $referencia;
    private $dsi;
    private $motivo;
    private $estilo;
    private $tipoDocs;
    private $idiomas;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add('usuario', 'hidden', array('mapped' => false, 'data' => $this->usuario))
            ->add('referencia', 'hidden', array('data' => $this->referencia))
            ->add('dsi', 'hidden', array('data' => $this->dsi))
            ->add('tema', 'textarea', array('required' => true))
            ->add('motivo', 'choice', array('choice_list' => $this->motivo,))
            ->add('estilo', 'choice', array('choice_list' => $this->estilo))
            ->add(
                'tiposDocs',
                'choice',
                array('required' => true, 'choice_list' => $this->tipoDocs, 'multiple' => true, 'expanded' => true)
            )
            ->add('idiomas', 'choice', array('required' => true, 'choice_list' => $this->idiomas, 'multiple' => true, 'expanded' => true))
            ->add('fechaDesde', 'text')
            ->add('fechaHasta', 'text')
            ->add('Guardar', 'submit')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\BibliografiaBundle\Entity\Bibliografia'
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
     * @param mixed $motivo
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
    }

    /**
     * @param mixed $estilo
     */
    public function setEstilo($estilo)
    {
        $this->estilo = $estilo;
    }

    /**
     * @param mixed $tipoDocs
     */
    public function setTipoDocs($tipoDocs)
    {
        $this->tipoDocs = $tipoDocs;
    }

    /**
     * @param mixed $idiomas
     */
    public function setIdiomas($idiomas)
    {
        $this->idiomas = $idiomas;
    }

    /**
     * @param mixed $referencia
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    /**
     * @param mixed $dsi
     */
    public function setDsi($dsi)
    {
        $this->dsi = $dsi;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formBibliografia';
    }
}