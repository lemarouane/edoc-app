<?php

namespace App\Form;

use App\Entity\Cvtheque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class CvthequeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emailPerso',TextType::class,array(
                'label' => 'email_perso',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',  
  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                    'required' => true
                ))
       
            ->add('mobile',TelType::class, [
                'label' => 'tel_obligatoire',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                'required' => true
                ])
         
            ->add('adressePostale',TextareaType::class,array(
                'label' => 'adresse_postale',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                ),
                
                ))


              

                ->add('permis',ChoiceType::class,array(
                    'label' => 'permis',
                    'attr' => array(
                        'class' => 'form-select',
                        'placeholder' => '',  
                    ),
                    'label_attr' => array(
                            'class' =>'form-label'
                    ),
                    'choices' => array('Pas de Permis'=>'NO' , 'A'=>'A' , 'B'=>'B' ,  'EB'=>'EB', 'C'=>'C' , 'EC'=>'EC' , 'D'=>'D' , 'ED'=>'ED'  )
                    
                    ))

            ->add('linkedin',TextType::class,array(
                'label' => 'c_linkedin',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '', 
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                ),
                
                'required' => false))

          
                ->add('langues',TextType::class,array(
                    'label' => 'langues',
                    'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => '', 
                    ),
                    'label_attr' => array(
                            'class' =>'form-label'
                    ),
                    
                    'required' => false))

                    ->add('distinctions',TextType::class,array(
                        'label' => 'distinctions',
                        'attr' => array(
                            'class' => 'form-control',
                            'placeholder' => '', 
                        ),
                        'label_attr' => array(
                                'class' =>'form-label'
                        ),
                        
                        'required' => false))

                        ->add('certifications',TextareaType::class,array(
                            'label' => 'certifications',
                            'attr' => array(
                                'class' => 'form-control',
                                'placeholder' => '', 
                            ),
                            'label_attr' => array(
                                    'class' =>'form-label'
                            ),
                            
                            'required' => false))

                            ->add('softskills',TextType::class,array(
                                'label' => 'softskills',
                                'attr' => array(
                                    'class' => 'form-control',
                                    'placeholder' => '', 
                                ),
                                'label_attr' => array(
                                        'class' =>'form-label'
                                ),
                                
                                'required' => false))


                                ->add('comp_technique',TextareaType::class,array(
                                    'label' => 'comp_technique',
                                    'attr' => array(
                                        'class' => 'form-control',
                                        'placeholder' => '', 
                                    ),
                                    'label_attr' => array(
                                            'class' =>'form-label'
                                    ),
                                    
                                    'required' => false))

                    ->add('hobbies',TextType::class,array(
                        'label' => 'hobbies',
                        'attr' => array(
                            'class' => 'form-control',
                            'placeholder' => '', 
                        ),
                        'label_attr' => array(
                                'class' =>'form-label'
                        ),
                        
                        'required' => false))



                ->add('experiences', CollectionType::class, [ 
                    'entry_type' => ExperienceType::class,  
                    'entry_options' => [
                      'label' => false,
                      'required' => false
                  ],
                  'by_reference' => false,
                  'allow_add' =>true,
                  'allow_delete' =>true,
                  ]) 

                  ->add('clubs', CollectionType::class, [ 
                    'entry_type' => ClubsType::class,  
                    'entry_options' => [
                      'label' => false,
                      'required' => false
                  ],
                  'by_reference' => false,
                  'allow_add' =>true,
                  'allow_delete' =>true,
                  ])

                  ->add('formations', CollectionType::class, [ 
                    'entry_type' => FormationsType::class,  
                    'entry_options' => [
                      'label' => false,
                      'required' => false
                  ],
                  'by_reference' => false,
                  'allow_add' =>true,
                  'allow_delete' =>true,
                  ])


/////////////////////

->add('emailPro',TextType::class,array(
    'label' => 'email_pro',
    'attr' => array(
        'class' => 'form-control',
        'placeholder' => '',  
    ),
    'label_attr' => array(
            'class' =>'form-label'
        ),
    
    'required' => false))


    ->add('situation',ChoiceType::class, array(
        'choices' => array('rech_emploi' => '1' , 'autre' => '0','act_pro' => '3', 'f_doctorale' => '2'),
        'label_attr' => [
            'class' => 'form-label',
        ],
        'required' => true,
        'label' => 'situation_actuel'
    ))
    ->add('disciplineDoc',TextType::class,array(
        'label' => 'discipline',
        'attr' => array(
            'class' => 'form-control',
            'placeholder' => '', 
        ),
        'label_attr' => array(
                'class' =>'form-label'
        ),
        'required' => false))
    ->add('cedDoc',TextType::class,array(
        'label' => 'spec',
        'attr' => array(
            'class' => 'form-control',
            'placeholder' => '', 
        ),
        'label_attr' => array(
                'class' =>'form-label'
        ),
        'required' => false))
    ->add('specialiteDoc',TextType::class,array(
        'label' => 'CED',
        'attr' => array(
            'class' => 'form-control',
            'placeholder' => '', 
        ),
        'label_attr' => array(
                'class' =>'form-label'
        ),
        'required' => false))
    ->add('universiteDoc',TextType::class,array(
        'label' => 'univ',
        'attr' => array(
            'class' => 'form-control',
            'placeholder' => '', 
        ),
        'label_attr' => array(
                'class' =>'form-label'
        ),
        'required' => false))
    ->add('autreSituation',TextType::class,array(
        'label' => 'autre_situation',
        'attr' => array(
            'class' => 'form-control',
            'placeholder' => '', 
        ),
        'label_attr' => array(
                'class' =>'form-label'
        ),
        'required' => false))
    ->add('delai',ChoiceType::class, array(
            'choices' => array('md_1' => '0','md_2' => '1', 'md_3' => '2' ),
            'label_attr' => [
                'class' => 'form-label',
            ],
            'required' => true,
            'label' => 'delais'
        ))
    ->add('paysVille',TextType::class,[
            'label' => 'pays_ville',
            'required' => false,
            'attr' => array(
                'class' => 'form-control', 
                'placeholder' => '',  
            ),
            'label_attr' => array(
                    'class' =>'form-label'
                ),
        ])

        ->add('organisme',TextType::class,[
            'label' => 'organisme_emp',
            'required' => false,
            'attr' => array(
                'class' => 'form-control', 
                'placeholder' => '',  
            ),
            'label_attr' => array(
                    'class' =>'form-label'
                ),
        ])
    ->add('poste',TextType::class,[
            'label' => 'întitule_post',
            'required' => false,
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
            'required' => false,
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => '',   
            ),
            'label_attr' => array(
                    'class' =>'form-label'
                ),
        ])
    ->add('rhPhone',TextType::class,[
            'label' => 'tel_rh',
            'required' => false,
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
            'required' => false,
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
            'required' => false,
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
            'data_class' => Cvtheque::class,
        ]);
    }
}
