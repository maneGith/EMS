<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
                ->add('localite')
                ->add('adresse',TextareaType::class)
                ->add('codeO')
                ->add('codeR')
            ->add('statut', ChoiceType::class, array(
                'choices'  => array(
                    'DG' =>'DG',
                    'AG' =>'AG',
                    'CH' =>'CH',
                    'PD' =>'PD',
                    'PR' =>'PR',
                ),
                'expanded' => true,
                'multiple' => false,
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Agence'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_agence';
    }


}
