<?php

namespace App\Form;

use App\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('intituleposte',TextType::class,[
            'label' => 'intituleposte',
            'attr' => array(
                'class' => 'form-control', 
                'placeholder' => '',  
            ),
            'label_attr' => array(
                    'class' =>'form-label'
                ),
            ])
        ->add('dateDebut',DateType::class, array('label' => 'date_debut','widget' => 'single_text',
                                                    'html5' => true,
                                                    'attr' => ['class' => 'result form-control js-dateDebut'],
                                                    'label_attr' => array(
                                                        'class' =>'form-label'
                                                        ),))
        ->add('dateFin',DateType::class, array('label' => 'date_fin','widget' => 'single_text','required'=>false,
                                                    'html5' => true,
                                                    'attr' => ['class' => ' result form-control js-dateFin'],
                                                    'label_attr' => array(
                                                        'class' =>'form-label'
                                                    ),))
        ->add('entreprise',TextType::class,[
                'label' => 'entreprise',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('lieu',TextType::class,[
                'label' => 'lieu',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('descriptionmission',TextType::class,[
                'label' => 'descriptionmission',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',   
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('lienportfolio',TextType::class,[
                'label' => 'lienportfolio',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
      

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
