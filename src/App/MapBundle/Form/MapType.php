<?php

namespace App\MapBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MapType extends AbstractType
{
    private $idTask;
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['label' => 'Название карты:'])
            ->add('img', 'file', ['label' => 'Изображение карты:', 'required' => false])
            ->add('x', null, ['label' => 'Квадратов по X:'])
            ->add('y', null, ['label' => 'Квадратов по Y:'])
            ->add('maps', 'entity', ['label' => 'Для задания:', 'class' => 'AppMapBundle:Task', 'property_path' => 'maps'])
        ;
    }

    public function setTask($id){
        $this->idTask = $id;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\MapBundle\Entity\Map'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_mapbundle_map';
    }
}
