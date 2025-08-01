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
        ->add('paysVille',TextType::class,[
            'label' => 'pays_ville',
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
        ->add('organisme',TextType::class,[
                'label' => 'organisme_emp',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('poste',TextType::class,[
                'label' => 'intitule_poste',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('departement',TextType::class,[
                'label' => 'departement',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',   
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('rhPhone',TextType::class,[
                'label' => 'tel',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('rhEmail',TextType::class,[
                'label' => 'email_rh',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',   
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('rhContact',TextType::class,[
                'label' => 'contact_rh',
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
