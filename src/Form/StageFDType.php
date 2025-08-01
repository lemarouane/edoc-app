<?php

namespace App\Form;

use App\Entity\StageFD;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StageFDType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formDisabled = $options['form_disabled'] ?? false;
        $moduleChoices = $options['module_choices'] ?? [];

        $builder
            ->add('moduleId', ChoiceType::class, [
                'label' => 'Cadre de Stage',
                'choices' => $moduleChoices,
                'placeholder' => 'Sélectionner un module',
                'constraints' => [new NotBlank(['message' => 'Veuillez sélectionner un module.'])],
                'disabled' => $formDisabled,
            ])
            ->add('cadreStage', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip($moduleChoices),
                'placeholder' => false,
                'attr' => ['style' => 'display:none;'],
                'disabled' => $formDisabled,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date Début',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une date de début.'])],
                'disabled' => $formDisabled,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date Fin',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une date de fin.'])],
                'disabled' => $formDisabled,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StageFD::class,
            'form_disabled' => false,
            'module_choices' => [],
        ]);
    }
}
