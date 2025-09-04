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
        // General Information (Rapport d'Avancement de Thèse - Required Fields)
        $builder
            ->add('discipline', TextType::class, [
                'required' => true,
                'label' => 'Discipline',
                'attr' => ['class' => 'form-control']
            ])
            ->add('specialite', TextType::class, [
                'required' => true,
                'label' => 'Spécialité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('intituleThese', TextType::class, [
                'required' => true,
                'label' => 'Intitulé de la thèse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('introduction', TextareaType::class, [
                'required' => true,
                'label' => 'Introduction',
                'attr' => ['class' => 'form-control']
            ])
            ->add('problematique', TextareaType::class, [
                'required' => true,
                'label' => 'Problématique',
                'attr' => ['class' => 'form-control']
            ])
            ->add('methodologie', TextareaType::class, [
                'required' => true,
                'label' => 'Méthodologie',
                'attr' => ['class' => 'form-control']
            ])
            ->add('resultats', TextareaType::class, [
                'required' => true,
                'label' => 'Résultats obtenus et interprétation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('conclusion', TextareaType::class, [
                'required' => true,
                'label' => 'Conclusion',
                'attr' => ['class' => 'form-control']
            ])
            ->add('travauxEnAttente', TextareaType::class, [
                'required' => true,
                'label' => 'Travaux en attente',
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
            ->add('bourseProjetRecherche', TextType::class, [
                'required' => false,
                'label' => 'Bourse de projet de recherche (préciser)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('salarieFonction', TextType::class, [
                'required' => false,
                'label' => 'Salarié - Fonction',
                'attr' => ['class' => 'form-control']
            ])
            ->add('salarieOrganisme', TextType::class, [
                'required' => false,
                'label' => 'Salarié - Organisme employeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('fonctionnaireFonction', TextType::class, [
                'required' => false,
                'label' => 'Fonctionnaire - Fonction',
                'attr' => ['class' => 'form-control']
            ])
            ->add('fonctionnaireOrganisme', TextType::class, [
                'required' => false,
                'label' => 'Fonctionnaire - Organisme employeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('cotutelle', CheckboxType::class, [
                'required' => false,
                'label' => 'Co-Directeur de thèse (Cotutelle)',
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('cotutelleUniversite', TextType::class, [
                'required' => false,
                'label' => 'Université et/ou organisme de recherche',
                'attr' => ['class' => 'form-control']
            ])
            ->add('cotutelleNomPronom', TextType::class, [
                'required' => false,
                'label' => 'Nom et Prénom du Co-Directeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('cotutelleTel', TextType::class, [
                'required' => false,
                'label' => 'Téléphone du Co-Directeur',
                'attr' => ['class' => 'form-control']
            ])
            ->add('cotutelleEmail', TextType::class, [
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