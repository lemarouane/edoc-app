<?php

namespace App\Form;

use App\Entity\Etudiants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ProfileEtudiantsEditPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


         $builder
            ->add('oldPassword',PasswordType::class, array('mapped' => false , 'label' =>'Old_Pass'))

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'New_Pass'],
                'second_options' => ['label' => 'Confirm_Pass'],
                'mapped' => false,
            ]);
            

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etudiants::class,
        ]);
    }
}
