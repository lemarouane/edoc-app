<?php

namespace App\Form;

use App\Entity\ConventionSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ConventionSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $conventions = $options['conventions'] ?? [];
        $choices = [];
        foreach ($conventions as $convention) {
            $choices[$convention['etablissement']] = $convention['id'];
        }

        $builder
            ->add('conventionId', ChoiceType::class, [
                'label' => 'Convention',
                'choices' => $choices,
                'placeholder' => 'Sélectionner une convention',
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ])
            ->add('zipFile', FileType::class, [
                'label' => 'Fichier ZIP',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '100m',
                        'mimeTypes' => ['application/zip', 'application/x-zip-compressed'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier ZIP valide.',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
                'help' => 'Laissez vide pour conserver le fichier actuel lors de la modification.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConventionSubmission::class,
            'conventions' => [],
        ]);
    }
}