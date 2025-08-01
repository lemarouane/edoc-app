<?php

namespace App\Form;

use App\Entity\ReinscriptionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReinscriptionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // General Information
        $builder
            ->add('discipline', TextType::class, [
                'required' => false,
                'label' => 'Discipline',
                'attr' => ['class' => 'form-control']
            ])
            ->add('specialite', TextType::class, [
                'required' => false,
                'label' => 'Spécialité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('intituleThese', TextType::class, [
                'required' => false,
                'label' => 'Intitulé de la thèse',
                'attr' => ['class' => 'form-control']
            ])
        
            // Scholarships
            ->add('bourseMeriteDebut', DateTimeType::class, [
                'required' => false,
                'label' => 'Bourse de mérite, depuis',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bourse3emeCycleDebut', DateTimeType::class, [
                'required' => false,
                'label' => 'Bourse de 3ème cycle, depuis',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bourseCotutelleDebut', DateTimeType::class, [
                'required' => false,
                'label' => 'Bourse de cotutelle. Durée Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bourseCotutelleFin', DateTimeType::class, [
                'required' => false,
                'label' => 'Bourse de cotutelle. Durée Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bourseEchangeDetails', TextType::class, [
                'required' => false,
                'label' => 'Bourse d’échange (préciser)',
                'attr' => ['class' => 'form-control']
            ])
        
            // Work Completed
            ->add('introduction', TextareaType::class, [
                'required' => false,
                'label' => 'Introduction',
                'attr' => ['class' => 'form-control']
            ])
            ->add('problematique', TextareaType::class, [
                'required' => false,
                'label' => 'Problématique',
                'attr' => ['class' => 'form-control']
            ])
            ->add('methodologie', TextareaType::class, [
                'required' => false,
                'label' => 'Méthodologie',
                'attr' => ['class' => 'form-control']
            ])
            ->add('resultats', TextareaType::class, [
                'required' => false,
                'label' => 'Résultats obtenus et interprétation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('conclusion', TextareaType::class, [
                'required' => false,
                'label' => 'Conclusion',
                'attr' => ['class' => 'form-control']
            ])
        
            // Future Work
            ->add('futureWorkYear', TextType::class, [
                'required' => false,
                'label' => 'Travaux futurs année',
                'attr' => ['class' => 'form-control']
            ])
            ->add('futureWorkDetails', TextareaType::class, [
                'required' => false,
                'label' => 'Travaux futurs détails',
                'attr' => ['class' => 'form-control']
            ])
        
            // Complementary Training
            ->add('formations', TextareaType::class, [
                'required' => false,
                'label' => 'Formations Complémentaires',
                'attr' => ['class' => 'form-control']
            ])
        
            // Co-Director Info
            ->add('coDirecteurCotutelle', CheckboxType::class, [
                'required' => false,
                'label' => 'Co-Directeur de thèse (Cotutelle)',
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('coDirecteurUniversite', TextType::class, [
                'required' => false,
                'label' => 'Université et/ou organisme de recherche',
                'attr' => ['class' => 'form-control']
            ])
            ->add('coDirecteurNomPrenom', TextType::class, [
                'required' => false,
                'label' => 'Nom et Prénom du Co-Directeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('coDirecteurTel', TextType::class, [
                'required' => false,
                'label' => 'Téléphone du Co-Directeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('coDirecteurEmail', TextType::class, [
                'required' => false,
                'label' => 'Email du Co-Directeur',
                'attr' => ['class' => 'form-control']
            ])
        
            // Administrative Info
            ->add('responsableStructureNomPrenom', TextType::class, [
                'required' => false,
                'label' => 'Responsable de la structure d\'accueil',
                'attr' => ['class' => 'form-control']
            ])
            ->add('directeurTheseNomPrenom', TextType::class, [
                'required' => false,
                'label' => 'Nom et Prénom du Directeur de thèse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateEnvoi', DateTimeType::class, [
                'required' => false,
                'label' => 'Date d\'envoi',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
        
            ->add('save', SubmitType::class, [
                'label' => 'Soumettre la demande',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReinscriptionRequest::class,
        ]);
    }
}
