<?php

namespace App\Form;

use App\Entity\Entreprises;
use App\Entity\TypeStage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class stageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 

            ->add('entreprise' , EntityType::class, array(
                'label'=>'entreprise',
                'class' => Entreprises::class,
                'required' => false,
                'attr' => array(
                    'class' => 'form-select',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                   
                'placeholder' => '------------',
                'choice_label' => function ($entreprise) {
                    return $entreprise->getIntitule();
                }))
            ->add('typeStage', EntityType::class, array(
                'label'=>'type_stage',
                'class' => TypeStage::class,
                'attr' => array(
                    'class' => 'form-select',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                'placeholder' => '------------',
                'choice_label' => function ($type) {
                    return $type->getLibelle();
                }))
            ->add('sujet',TextareaType::class,[
                'label'=>'sujet',
                'attr' => array(
                    'class' => 'form-control',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
            ->add('intitule',TextType::class,[
                'label'=>'intitule',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
            ->add('dateDebut',DateType::class, array('widget' => 'single_text','required'=>true,
            'label'=>'date_debut',
                                                      'html5' => false,
                                                      'attr' => ['class' => 'result form-control js-dateDebut'],
                                                      'label_attr' => array(
                                                        'class' =>'form-label'
                                                        ),))
            ->add('dateFin',   DateType::class, array('widget' => 'single_text','required'=>true,
            'label'=>'date_fin',
                                                      'html5' => false,
                                                      'attr' => ['class' => ' result form-control js-dateFin'],
                                                      'label_attr' => array(
                                                        'class' =>'form-label'
                                                    ),))
            ->add('phone',TextType::class,[
                'label'=>'tel',
                'attr' => array(
                    'class' => 'form-control',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ])
            ->add('fichier',FileType::class, array(
                  'label' => 'uploader_fichier',
                  'data_class' => null,
                  'required' => false,
                  'attr' => array(
                    'class' => 'form-control',  
                    ),
                  'label_attr' => array(
                        'class' =>'form-label'
                    ),
            ));
                
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Stage',

        ));
    }

    public function getName()
    {
        return 'stagetype';
    }
}
