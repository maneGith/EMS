<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbonneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
            ->add('civilite', ChoiceType::class, array(
                'choices'  => array(
                    'Entreprise' =>'Entreprise',
                    'Particulier' =>'Particulier',
                ),
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('telephone')
            ->add('email')
            ->add('ville')
            ->add('codepostal')
            ->add('adresse',TextareaType::class)
            ->add('numcompte')
            ->add('etat', ChoiceType::class, array(
                'choices'  => array(
                    'Maintenu' =>'Maintenu',
                    'Résilié' =>'Résilié',
                ),
                'expanded' => true,
                'multiple' => false,
            ))->add('type', ChoiceType::class, array(
                'choices'  => array(
                    'ANCIEN CLIENT' =>'ANCIEN CLIENT',
                    'NOUVEAU CLIENT' =>'NOUVEAU CLIENT',
                    'INSTITUT PASTEUR' =>'INSTITUT PASTEUR',
                    'EDITION 3 FLEUVES' =>'EDITION 3 FLEUVES',
                ),
                'expanded' => false,
                'multiple' => false,
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Abonne'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_abonne';
    }


}
