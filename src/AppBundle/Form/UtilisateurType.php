<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom', TextType::class)
                 ->add('email', EmailType::class)

                 ->add('profil',  ChoiceType::class,array(
                        'choices'=>array('DGEMS'=>'DGEMS',
                                         'DEXEMS'=>'DEXEMS',
                                         'DAFEMS'=>'DAFEMS',
                                         'CONTROLE'=>'CONTROLE',
                                         'RECOUVREMENT'=>'RECOUVREMENT',
                                         'CHEFDAGENCE'=>'CHEFDAGENCE',
                                         'AGENTGUICHET'=>'AGENTGUICHET',
                                         'AGENTCABINE'=>'AGENTCABINE',)))
                 ->add('agence',EntityType::class,array(
                    'class' =>'AppBundle\Entity\Agence',
                    'choice_label' =>'nom',
                    'expanded' =>false,
                    'multiple' =>false
                 ))
                 ->add('username', TextType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Utilisateur'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_utilisateur';
    }


}
