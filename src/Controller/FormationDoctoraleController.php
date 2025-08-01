<?php

namespace App\Controller;

use App\Entity\FormationDoctorale;
use App\Entity\Module;
use App\Entity\StageFD;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Constraints\File;
 use Symfony\Component\Form\Extension\Core\Type\DateType;
 use Symfony\Component\Validator\Constraints\NotBlank;
 use Symfony\Component\Validator\Constraints\Date;
  
class FormationDoctoraleController extends AbstractController
{
    #[Route('/formation-doctorale', name: 'formation_doctorale', methods: ['GET', 'POST'])]
    public function index(Security $security, Request $request, EntityManagerInterface $em): Response
    {
        $usr = $security->getUser();

        if ($usr->getType() !== 'FD') {
            $this->addFlash('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
        $cin = $usr->getCode();

        $stmt = $connCustomer->prepare("
            SELECT d.id, d.nom, d.prenom 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();

        if (!$doctorant) {
            $this->addFlash('danger', 'Données du doctorant introuvables ou non validées.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $doctorantId = $doctorant['id'];
        $doctorantFullname = $doctorant['nom'] . ' ' . $doctorant['prenom'];

        // Fetch the latest formation request (still useful for status display)
        $latestFormation = $em->getRepository(FormationDoctorale::class)
            ->findOneBy(['doctorant_id' => $doctorantId], ['created_at' => 'DESC']);

        $modules = $em->getRepository(Module::class)->findAll();
        $moduleChoices = [];
        foreach ($modules as $module) {
            $moduleChoices[$module->getIntitule()] = $module;
        }

        $formation = new FormationDoctorale();
        $formation->setDoctorantId($doctorantId);
        $formation->setDoctorantFullname($doctorantFullname);
        $formation->setStatus('En cours');

        $form = $this->createFormBuilder($formation)
            ->add('module', ChoiceType::class, [
                'choices' => $moduleChoices,
                'choice_label' => function ($module) {
                    return $module->getIntitule();
                },
                'label' => 'Module',
            ])
            ->add('vol_horaire', IntegerType::class, ['label' => 'Volume Horaire'])
            ->add('intitule_formation', TextType::class, ['label' => 'Intitulé de la Formation'])
            ->add('organisme_formation', TextType::class, ['label' => 'Organisme de Formation'])
            ->add('pieceJointe', FileType::class, [
                'label' => 'Pièce Jointe (PDF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide.',
                    ])
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formation->setModuleIntitule($formation->getModule()->getIntitule());
            $formation->setCreatedAt(new \DateTime());

            $uploadDir = 'public/uploads/pdf';
            $file = $form->get('pieceJointe')->getData();
            if ($file) {
                $newFilename = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
                try {
                    $file->move($uploadDir, $newFilename);
                    $formation->setPieceJointe($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier.');
                    return new RedirectResponse($this->generateUrl('formation_doctorale'));
                }
            }

            $em->persist($formation);
            $em->flush();

            $this->addFlash('success', 'Formation doctorale enregistrée avec succès.');
            return new RedirectResponse($this->generateUrl('formation_doctorale'));
        }

        $formations = $em->getRepository(FormationDoctorale::class)->findBy(['doctorant_id' => $doctorantId]);

        return $this->render('formation_doctorale/index.html.twig', [
            'form' => $form->createView(),
            'formations' => $formations,
            'latestFormation' => $latestFormation,
            'doctorant_id' => $doctorantId
        ]);
    }

    #[Route('/formation-doctorale/edit/{id}', name: 'formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FormationDoctorale $formation, EntityManagerInterface $em, Security $security): Response
    {
        $usr = $security->getUser();

        if ($usr->getType() !== 'FD') {
            $this->get('session')->getFlashBag()->add('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
        $cin = $usr->getCode();

        $stmt = $connCustomer->prepare("
            SELECT d.id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();



        // Check status before allowing edit
        if ($formation->getStatus() !== 'En cours') {
            $this->get('session')->getFlashBag()->add('danger', 'Cette formation ne peut plus être modifiée.');
            return new RedirectResponse($this->generateUrl('formation_doctorale'));
        }

        $modules = $em->getRepository(Module::class)->findAll();
        $moduleChoices = [];
        foreach ($modules as $module) {
            $moduleChoices[$module->getIntitule()] = $module;
        }

        $form = $this->createFormBuilder($formation)
            ->add('module', ChoiceType::class, [
                'choices' => $moduleChoices,
                'choice_label' => function ($module) {
                    return $module->getIntitule();
                },
                'label' => 'Module',
            ])
            ->add('vol_horaire', IntegerType::class, ['label' => 'Volume Horaire'])
            ->add('intitule_formation', TextType::class, ['label' => 'Intitulé de la Formation'])
            ->add('organisme_formation', TextType::class, ['label' => 'Organisme de Formation'])
            ->add('pieceJointe', FileType::class, [
                'label' => 'Pièce Jointe (PDF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide.',
                    ])
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formation->setModuleIntitule($formation->getModule()->getIntitule());

            $uploadDir = 'public/uploads/pdf';
            $file = $form->get('pieceJointe')->getData();
            if ($file) {
                $newFilename = sha1(uniqid(mt_rand(), true)) . '.' . $file->guessExtension();
                try {
                    if ($formation->getPieceJointe() && file_exists($uploadDir . '/' . $formation->getPieceJointe())) {
                        unlink($uploadDir . '/' . $formation->getPieceJointe());
                    }
                    $file->move($uploadDir, $newFilename);
                    $formation->setPieceJointe($newFilename);
                } catch (FileException $e) {
                    $this->get('session')->getFlashBag()->add('danger', 'Erreur lors de l\'upload du fichier.');
                    return new RedirectResponse($this->generateUrl('formation_doctorale'));
                }
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Formation doctorale modifiée avec succès.');
            return new RedirectResponse($this->generateUrl('formation_doctorale'));
        }

        return $this->render('formation_doctorale/edit.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    #[Route('/formation-doctorale/delete/{id}', name: 'formation_delete', methods: ['POST'])]
    public function delete(Request $request, FormationDoctorale $formation, EntityManagerInterface $em, Security $security): Response
    {
        $usr = $security->getUser();

        if ($usr->getType() !== 'FD') {
            $this->get('session')->getFlashBag()->add('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
        $cin = $usr->getCode();

        $stmt = $connCustomer->prepare("
            SELECT d.id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();


        // Check status before allowing delete
        if ($formation->getStatus() !== 'En cours') {
            $this->get('session')->getFlashBag()->add('danger', 'Cette formation ne peut plus être supprimée.');
            return new RedirectResponse($this->generateUrl('formation_doctorale'));
        }

        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
            $uploadDir = 'public/uploads/pdf';
            if ($formation->getPieceJointe() && file_exists($uploadDir . '/' . $formation->getPieceJointe())) {
                unlink($uploadDir . '/' . $formation->getPieceJointe());
            }
            $em->remove($formation);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Formation doctorale supprimée avec succès.');
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'Token CSRF invalide.');
        }

        return new RedirectResponse($this->generateUrl('formation_doctorale'));
    }

    #[Route('/formation-doctorale/file/{id}', name: 'formation_file', methods: ['GET'])]
    public function viewFile(FormationDoctorale $formation, Security $security): Response
    {
        $usr = $security->getUser();

        if ($usr->getType() !== 'FD') {
            $this->get('session')->getFlashBag()->add('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
        $cin = $usr->getCode();

        $stmt = $connCustomer->prepare("
            SELECT d.id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();



        $filePath = '/e-doc/public/public/uploads/pdf/' . $formation->getPieceJointe();
        if (!file_exists($filePath)) {
            $this->get('session')->getFlashBag()->add('danger', 'Fichier non trouvé.');
            return new RedirectResponse($this->generateUrl('formation_doctorale'));
        }

        return new BinaryFileResponse($filePath);
    }




private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/my-stage-fd', name: 'my_stage_fd', methods: ['GET', 'POST'])]
    public function myStageFD(Request $request, EntityManagerInterface $em): Response
    {
        $usr = $this->security->getUser();
        if ($usr->getType() !== 'FD') {
            $this->addFlash('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $customerConnection = $this->getDoctrine()->getConnection('customer');
        $cin = $usr->getCode();

        $stmt = $customerConnection->prepare("
            SELECT d.id, d.nom, d.prenom 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();

        if (!$doctorant) {
            $this->addFlash('danger', 'Données du doctorant introuvables ou non validées.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }
        $doctorantId = $doctorant['id'];
        $doctorantFullname = $doctorant['nom'] . ' ' . $doctorant['prenom'];

        // Fetch modules
        $modules = $em->getConnection()->executeQuery("SELECT id, intitule FROM module")->fetchAllAssociative();
        $moduleChoices = array_combine(
            array_column($modules, 'intitule'),
            array_column($modules, 'id')
        );

        
        // Fetch stages, ordered by ID DESC to get latest first
        $stages = $em->getRepository(StageFD::class)->findBy(
            ['doctorantId' => $doctorantId],
            ['id' => 'DESC'] // Sort by ID descending for latest stage
        );

        foreach ($stages as $stage) {
            if ($stage->getLettreAcceptation()) {
                $stage->pdfPath = '/uploads/pdf/' . $stage->getLettreAcceptation();
            }
        }

        // Check latest stage status for form restriction
        $latestStage = $stages[0] ?? null;
        $formDisabled = $latestStage && $latestStage->getStatus() === 'Validé par directeur adjoint';

        // Create form with validation
        $stageFD = new StageFD();
        $form = $this->createFormBuilder($stageFD, ['attr' => ['novalidate' => 'novalidate']])
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
            ])
            ->add('lettreAcceptation', FileType::class, [
                'label' => 'Lettre d’Acceptation',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide.',
                    ]),
                ],
                'disabled' => $formDisabled,
            ])
            ->add('lieuStage', TextType::class, [
                'label' => 'Lieu de Stage',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un lieu de stage.'])],
                'disabled' => $formDisabled,
            ])
            ->add('entiteHebergante', TextType::class, [
                'label' => 'Entité Hébergeante',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une entité hébergeante.'])],
                'disabled' => $formDisabled,
            ])
            ->getForm();

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$formDisabled) {
            $data = $form->getData();
            $file = $form->get('lettreAcceptation')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/pdf', $filename);
                $data->setLettreAcceptation($filename);
            }

            $data->setDoctorantId($doctorantId);
            $data->setDoctorantFullname($doctorantFullname);

            $em->persist($data);
            $em->flush();

            $this->addFlash('success', 'Stage soumis avec succès.');
            return $this->redirectToRoute('my_stage_fd');
        } elseif ($form->isSubmitted() && $formDisabled) {
            $this->addFlash('error', 'Vous ne pouvez pas soumettre une nouvelle demande car votre dernière demande a été validée.');
        }

        return $this->render('formation_doctorale/my_stage_fd.html.twig', [
            'form' => $form->createView(),
            'stages' => $stages,
            'doctorant_id' => $doctorantId,
            'form_disabled' => $formDisabled,
        ]);
    }

    #[Route('/stage/edit/{id}', name: 'stage_edit', methods: ['GET', 'POST'])]
    public function editStage(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $usr = $this->security->getUser();
        if ($usr->getType() !== 'FD') {
            $this->addFlash('danger', 'Accès réservé aux doctorants.');
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }

        $stage = $em->getRepository(StageFD::class)->find($id);
        if (!$stage) {
            throw $this->createNotFoundException('Stage not found');
        }

        $customerConnection = $this->getDoctrine()->getConnection('customer');
        $cin = $usr->getCode();

        $stmt = $customerConnection->prepare("
            SELECT d.id, d.nom, d.prenom 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();

        if (!$doctorant || $stage->getDoctorantId() !== $doctorant['id'] || $stage->getStatus() !== 'En cours') {
            $this->addFlash('error', 'Vous ne pouvez pas modifier ce stage.');
            return $this->redirectToRoute('my_stage_fd');
        }
        $doctorantFullname = $doctorant['nom'] . ' ' . $doctorant['prenom'];

        $modules = $em->getConnection()->executeQuery("SELECT id, intitule FROM module")->fetchAllAssociative();
        $moduleChoices = array_combine(
            array_column($modules, 'intitule'),
            array_column($modules, 'id')
        );

        $form = $this->createFormBuilder($stage)
            ->add('moduleId', ChoiceType::class, [
                'label' => 'Cadre de Stage',
                'choices' => $moduleChoices,
                'placeholder' => 'Sélectionner un module',
                'constraints' => [new NotBlank(['message' => 'Veuillez sélectionner un module.'])],
            ])
            ->add('cadreStage', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip($moduleChoices),
                'placeholder' => false,
                'attr' => ['style' => 'display:none;'],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date Début',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une date de début.'])],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date Fin',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une date de fin.'])],
            ])
            ->add('lettreAcceptation', FileType::class, [
                'label' => 'Lettre d’Acceptation',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5m',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide.',
                    ]),
                ],
            ])
            ->add('lieuStage', TextType::class, [
                'label' => 'Lieu de Stage',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un lieu de stage.'])],
            ])
            ->add('entiteHebergante', TextType::class, [
                'label' => 'Entité Hébergeante',
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer une entité hébergeante.'])],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('lettreAcceptation')->getData();
            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf';
                if ($stage->getLettreAcceptation() && file_exists($uploadDir . '/' . $stage->getLettreAcceptation())) {
                    unlink($uploadDir . '/' . $stage->getLettreAcceptation());
                }
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($uploadDir, $filename);
                $stage->setLettreAcceptation($filename);
            }

            $stage->setDoctorantFullname($doctorantFullname);
            $em->flush();
            $this->addFlash('success', 'Stage modifié avec succès.');
            return $this->redirectToRoute('my_stage_fd');
        }

        return $this->render('formation_doctorale/edit_stage.html.twig', [
            'form' => $form->createView(),
            'stage' => $stage,
        ]);
    }

    #[Route('/stage/delete/{id}', name: 'stage_delete', methods: ['POST'])]
    public function deleteStage(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $usr = $this->security->getUser();
        if ($usr->getType() !== 'FD') {
            return new Response('Accès réservé aux doctorants.', 403);
        }

        $stage = $em->getRepository(StageFD::class)->find($id);
        if (!$stage) {
            return new Response('Stage not found', 404);
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $stage->getId(), $token)) {
            return new Response('Invalid CSRF token', 400);
        }

        $customerConnection = $this->getDoctrine()->getConnection('customer');
        $cin = $usr->getCode();

        $stmt = $customerConnection->prepare("
            SELECT d.id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();

        if (!$doctorant || $stage->getDoctorantId() !== $doctorant['id'] || $stage->getStatus() !== 'En cours') {
            return new Response('Unauthorized', 403);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf';
        if ($stage->getLettreAcceptation() && file_exists($uploadDir . '/' . $stage->getLettreAcceptation())) {
            unlink($uploadDir . '/' . $stage->getLettreAcceptation());
        }

        $em->remove($stage);
        $em->flush();

        return new Response('Stage deleted', 200);
    }
}

