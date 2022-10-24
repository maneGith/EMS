<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnvoiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('valeur')
                ->add('description',TextareaType::class)
                ->add('expediteur', ExpediteurType::class)
                ->add('destinataire', DestinataireType::class)
                ->add('codeenvoi', TextType::class)
                ->add('modepaie', ChoiceType::class, array(
                        'choices'  => array(
                            'Espèces' =>'Espèces',
                            'Bon prépayé' =>'Bon prépayé',
                            'Facture' =>'Facture',
                            'Autres' =>'Autres',
                        ),
                        'expanded' => true,
                        'multiple' => false,))
                ->add('nature', ChoiceType::class, array(
                        'choices'  => array(
                            'Cadeau' =>'Cadeau',
                            'Marchandise' =>'Marchandise',
                            'Autre' =>'Autre',
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
            'data_class' => 'AppBundle\Entity\Envoi'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_envoi';
    }


}
