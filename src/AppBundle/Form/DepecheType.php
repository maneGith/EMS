<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepecheType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('destination',EntityType::class,array(
                    'class' =>'AppBundle\Entity\Agence',
                    'choice_label' =>'nom',
                    'expanded' =>false,
                    'multiple' =>false
                 ))
                ->add('type',  ChoiceType::class,array(
                        'choices'=>array('dépêche des envois nationaux'=>'dépêche des envois nationaux',
                                         'dépêche des envois internationaux'=>'dépêche des envois internationaux'
                                        )))->add('heure')
                ->add('journee');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Depeche'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_depeche';
    }


}
