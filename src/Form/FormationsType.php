<?php

namespace App\Form;

use App\Entity\Formations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('annee',TextType::class,[
            'label' => 'annee',
            'attr' => array(
                'class' => 'form-control', 
                'placeholder' => '',  
            ),
            'label_attr' => array(
                    'class' =>'form-label'
                ),
            ])


        ->add('specialite',TextType::class,[
                'label' => 'specialite',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])

        ->add('diplome',TextType::class,[
                'label' => 'diplome',
                'attr' => array(
                    'class' => 'form-control', 
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
        ->add('filiere',TextType::class,[
                'label' => 'filiere',
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
            'data_class' => Formations::class,
        ]);
    }
}
