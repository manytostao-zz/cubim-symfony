<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 11/07/14
 * Time: 23:37
 */

namespace BMN\UsuarioBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use BMN\UsuarioBundle\Controller\DefaultController;
use BMN\UsuarioBundle\Entity\UsuarioRepository;
use BMN\UsuarioBundle\Entity\Usuario;
use BMN\UsuarioBundle\Form\DataTransformer\NomencladorToNumberTransformer;


/**
 * Class UsuarioType
 * @package BMN\UsuarioBundle\Form
 */
class UsuarioType extends AbstractType
{
    /**
     * @var
     */
    private $action;
    /**
     * @var
     */
    private $id;
    /**
     * @var
     */
    private $modulo;
    /**
     * @var
     */
    private $carnetBibOptions;
    /**
     * @var
     */
    private $carnetIdOptions;
    /**
     * @var
     */
    private $nombresOptions;
    /**
     * @var
     */
    private $apellidosOptions;
    /**
     * @var
     */
    private $paisOptions;
    /**
     * @var
     */
    private $emailOptions;
    /**
     * @var
     */
    private $estudianteOptions;
    /**
     * @var
     */
    private $tipoProOptions;
    /**
     * @var
     */
    private $tipoUsuaOptions;
    /**
     * @var
     */
    private $telefonoOptions;
    /**
     * @var
     */
    private $profesionOptions;
    /**
     * @var
     */
    private $categOcupOptions;
    /**
     * @var
     */
    private $categCienOptions;
    /**
     * @var
     */
    private $categDocOptions;
    /**
     * @var
     */
    private $categInvOptions;
    /**
     * @var
     */
    private $cargoOptions;
    /**
     * @var
     */
    private $institucionOptions;
    /**
     * @var
     */
    private $dedicacionOptions;
    /**
     * @var
     */
    private $experienciaOptions;
    /**
     * @var
     */
    private $especialidadOptions;
    /**
     * @var
     */
    private $fechaInsOptions;
    /**
     * @var
     */
    private $observacionesOptions;
    /**
     * @var
     */
    private $temaInvOptions;
    /**
     * @var
     */
    private $atendidoPorOptions;
    /**
     * @var
     */
    private $insideOptions;
    /**
     * @var
     */
    private $currentlyInNavOptions;
    /**
     * @var
     */
    private $currentlyInLectOptions;
    /**
     * @var
     */
    private $inactivo;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * UsuarioType constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction($this->action)
            ->add('tipoForm', 'hidden', array('data' => 1, 'mapped' => false))
            ->add('id', 'hidden', array('mapped' => false, 'data' => $this->id))
            ->add('modulo', 'hidden', array('mapped' => false, 'data' => $this->modulo))
            ->add('nombres', 'text', $this->nombresOptions)
            ->add('apellidos', 'text', $this->apellidosOptions)
            ->add('pais', 'text', $this->paisOptions)
            ->add('carnetBib', 'number', $this->carnetBibOptions)
            ->add('carnetId', 'text', $this->carnetIdOptions)
            ->add('email', 'email', $this->emailOptions)
            ->add('telefono', 'number', $this->telefonoOptions)
            ->add('estudiante', 'checkbox', $this->estudianteOptions)
            ->add('tipoPro', 'text', $this->tipoProOptions)
            ->add('tipoUsua', 'text', $this->tipoUsuaOptions)
            ->add('especialidad', 'text', $this->especialidadOptions)
            ->add('profesion', 'text', $this->profesionOptions)
            ->add('categOcup', 'text', $this->categOcupOptions)
            ->add('categCien', 'text', $this->categCienOptions)
            ->add('categDoc', 'text', $this->categDocOptions)
            ->add('categInv', 'text', $this->categInvOptions)
            ->add('cargo', 'text', $this->cargoOptions)
            ->add('institucion', 'text', $this->institucionOptions)
            ->add('dedicacion', 'text', $this->dedicacionOptions)
            ->add('experiencia', 'number', $this->experienciaOptions)
            ->add('fechaIns', 'birthday', $this->fechaInsOptions)
            ->add(
                'fechaInsDesde',
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
                'fechaInsHasta',
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
            ->add('fechaInsOper', 'hidden', array('mapped' => false, 'data' => '>='))
            ->add('observaciones', 'textarea', $this->observacionesOptions)
            ->add('temaInv', 'textarea', $this->temaInvOptions)
            ->add('atendidoPor', 'choice', $this->atendidoPorOptions)
            ->add('inside', 'checkbox', $this->insideOptions)
            ->add('currentlyInNav', 'checkbox', $this->currentlyInNavOptions)
            ->add('currentlyInLect', 'checkbox', $this->currentlyInLectOptions)
            ->add('inactivo', 'checkbox', $this->inactivo);

        $builder->get('pais')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('tipoPro')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('profesion')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('cargo')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('dedicacion')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('especialidad')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('categCien')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('categDoc')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('categInv')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('categOcup')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('tipoUsua')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
        $builder->get('institucion')->addModelTransformer(new \BMN\NomencladorBundle\Form\DataTransformer\NomencladorToNumberTransformer($this->manager));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BMN\UsuarioBundle\Entity\Usuario'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'form';
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $apellidosOptions
     */
    public function setApellidosOptions($apellidosOptions)
    {
        $this->apellidosOptions = $apellidosOptions;
    }

    /**
     * @param mixed $paisOptions
     */
    public function setPaisOptions($paisOptions)
    {
        $this->paisOptions = $paisOptions;
    }

    /**
     * @param mixed $emailOptions
     */
    public function setEmailOptions($emailOptions)
    {
        $this->emailOptions = $emailOptions;
    }

    /**
     * @param mixed $nombresOptions
     */
    public function setNombresOptions($nombresOptions)
    {
        $this->nombresOptions = $nombresOptions;
    }

    /**
     * @param mixed $carnetBibOptions
     */
    public function setCarnetBibOptions($carnetBibOptions)
    {
        $this->carnetBibOptions = $carnetBibOptions;
    }

    /**
     * @param mixed $carnetIdOptions
     */
    public function setCarnetIdOptions($carnetIdOptions)
    {
        $this->carnetIdOptions = $carnetIdOptions;
    }

    /**
     * @param mixed $telefonoOptions
     */
    public function setTelefonoOptions($telefonoOptions)
    {
        $this->telefonoOptions = $telefonoOptions;
    }

    /**
     * @param mixed $tipoProOptions
     */
    public function setTipoProOptions($tipoProOptions)
    {
        $this->tipoProOptions = $tipoProOptions;
    }

    /**
     * @param mixed $cargoOptions
     */
    public function setCargoOptions($cargoOptions)
    {
        $this->cargoOptions = $cargoOptions;
    }

    /**
     * @param mixed $categCienOptions
     */
    public function setCategCienOptions($categCienOptions)
    {
        $this->categCienOptions = $categCienOptions;
    }

    /**
     * @param mixed $categDocOptions
     */
    public function setCategDocOptions($categDocOptions)
    {
        $this->categDocOptions = $categDocOptions;
    }

    /**
     * @param mixed $categInvOptions
     */
    public function setCategInvOptions($categInvOptions)
    {
        $this->categInvOptions = $categInvOptions;
    }

    /**
     * @param mixed $categOcupOptions
     */
    public function setCategOcupOptions($categOcupOptions)
    {
        $this->categOcupOptions = $categOcupOptions;
    }

    /**
     * @param mixed $dedicacionOptions
     */
    public function setDedicacionOptions($dedicacionOptions)
    {
        $this->dedicacionOptions = $dedicacionOptions;
    }

    /**
     * @param mixed $especialidadOptions
     */
    public function setEspecialidadOptions($especialidadOptions)
    {
        $this->especialidadOptions = $especialidadOptions;
    }

    /**
     * @param mixed $experienciaOptions
     */
    public function setExperienciaOptions($experienciaOptions)
    {
        $this->experienciaOptions = $experienciaOptions;
    }

    /**
     * @param mixed $institucionOptions
     */
    public function setInstitucionOptions($institucionOptions)
    {
        $this->institucionOptions = $institucionOptions;
    }

    /**
     * @param mixed $profesionOptions
     */
    public function setProfesionOptions($profesionOptions)
    {
        $this->profesionOptions = $profesionOptions;
    }


    /**
     * @param mixed $fechaInsOptions
     */
    public function setFechaInsOptions($fechaInsOptions)
    {
        $this->fechaInsOptions = $fechaInsOptions;
    }

    /**
     * @param mixed $tipoUsuaOptions
     */
    public function setTipoUsuaOptions($tipoUsuaOptions)
    {
        $this->tipoUsuaOptions = $tipoUsuaOptions;
    }

    /**
     * @param mixed $observacionesOptions
     */
    public function setObservacionesOptions($observacionesOptions)
    {
        $this->observacionesOptions = $observacionesOptions;
    }

    /**
     * @param mixed $temaInvOptions
     */
    public function setTemaInvOptions($temaInvOptions)
    {
        $this->temaInvOptions = $temaInvOptions;
    }

    /**
     * @param mixed $atendidoPorOptions
     */
    public function setAtendidoPorOptions($atendidoPorOptions)
    {
        $this->atendidoPorOptions = $atendidoPorOptions;
    }

    /**
     * @param mixed $estudianteOptions
     */
    public function setEstudianteOptions($estudianteOptions)
    {
        $this->estudianteOptions = $estudianteOptions;
    }

    /**
     * @param mixed $modulo
     */
    public function setModulo($modulo)
    {
        $this->modulo = $modulo;
    }

    /**
     * @param mixed $insideOptions
     */
    public function setInsideOptions($insideOptions)
    {
        $this->insideOptions = $insideOptions;
    }

    /**
     * @param mixed $currentlyInNavOptions
     */
    public function setCurrentlyInNavOptions($currentlyInNavOptions)
    {
        $this->currentlyInNavOptions = $currentlyInNavOptions;
    }

    /**
     * @param mixed $currentlyInLectOptions
     */
    public function setCurrentlyInLectOptions($currentlyInLectOptions)
    {
        $this->currentlyInLectOptions = $currentlyInLectOptions;
    }

    /**
     * @param mixed $inactivo
     */
    public function setInactivo($inactivo)
    {
        $this->inactivo = $inactivo;
    }

} 