<?php

namespace App\Form;

use App\Entity\Etudiants;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use App\Form\imageType;
class ProfileEtudiantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


         $builder
            ->add('nom',TextType::class, [
                'label' => 'nom' ,
                'attr' => array(
                            'class' => 'form-control',  
                        ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                ])
            ->add('prenom',TextType::class, [
                'label' => 'prenom',
                'attr' => array(
                    'class' => 'form-control',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                    ])
            ->add('phone',TelType::class, [
                'label' => 'tel',
                'attr' => array(
                    'class' => 'form-control',  
                ),
                'label_attr' => array(
                        'class' =>'form-label'
                    ),
                ])
            ->add('image' , imageType::class , array('required' => false))


           ->add('locale',ChoiceType::class, array(
                'choices' => array('Français' => 'fr-FR', 'English' => 'en-GB' , 'عربي' => 'ar-AR', 'Español' => 'es-ES'),
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'required' => true,
                'placeholder' => '-----',
                'label' => 'locale'
            ))




        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etudiants::class,
        ]);
    }
    public function getName()
    {
        return 'ProfileEtudiantsType';
    }
}
