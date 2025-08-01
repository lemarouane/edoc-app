<?php

namespace App\Form;

use App\Entity\Etudiants;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use App\Form\imageType;

class profileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


         $builder
            
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
        return 'profileType';
    }
}
