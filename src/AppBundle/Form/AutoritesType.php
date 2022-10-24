<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutoritesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')->add('titre', ChoiceType::class, array(
            'choices'  => array(
                'DGTITULAIRE' =>'DGTITULAIRE',
                'DGINTERIM' =>'DGINTERIM',
                'DAFCTITULAIRE' =>'DAFCTITULAIRE',
                'DAFCINTERIM' =>'DAFCINTERIM',
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
            'data_class' => 'AppBundle\Entity\Autorites'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_autorites';
    }


}
