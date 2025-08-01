<?php

namespace App\Controller;

use App\Entity\Etudiant\ConventionDD;
use App\Entity\Etudiant\Etudiants;
use App\Entity\Etudiant\EtudiantDD;
use App\Entity\Etudiant\InscritEtudiant;
use App\Entity\Utilisateurs;
use App\Form\ConventionDDType;
use App\Form\EtudiantDDType;
use App\Repository\ConventionDDRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\InternetTest;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Psr\Log\LoggerInterface;


class ConventionSubmissionController extends AbstractController
{
    #[Route('/convention-submission', name: 'convention_submission')]
    public function index(Security $security, Connection $conn, EntityManagerInterface $em, LoggerInterface $logger, SessionInterface $session): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $logger->debug('No user logged in');
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $code = $user->getCode();
        if (!$code) {
            $logger->debug('User code missing');
            $this->addFlash('error', 'Code utilisateur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $logger->debug('User code: ' . $code . ', User ID: ' . $user->getId());

        // Fetch student's filiere from the most recent year
        $emDefault = $this->getDoctrine()->getManager('default');
        $connDefault = $emDefault->getConnection();
        $stmt = $connDefault->prepare("SELECT code FROM etudiants WHERE code = :code");
        $etudiant = $stmt->executeQuery(['code' => $code])->fetchAssociative();

        if (!$etudiant) {
            $logger->debug('No etudiant found for code: ' . $code);
            $this->addFlash('error', 'Étudiant introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $emEtudiant = $this->getDoctrine()->getManager('etudiant');
        $connEtudiant = $emEtudiant->getConnection();
        $stmt = $connEtudiant->prepare("SELECT COD_IND FROM individu WHERE COD_ETU = :cod_etu");
        $individu = $stmt->executeQuery(['cod_etu' => $code])->fetchAssociative();

        if (!$individu) {
            $logger->debug('No individu found for COD_ETU: ' . $code);
            $this->addFlash('error', 'Aucun individu trouvé pour ce COD_ETU.');
            return $this->redirectToRoute('app_login');
        }

        $codInd = $individu['COD_IND'];
        $logger->debug('COD_IND: ' . $codInd);

        // Fetch the most recent COD_ETP
        $stmt = $connEtudiant->prepare("
            SELECT COD_ETP, COD_ANU, DAT_CRE_IAE
            FROM ins_adm_etp 
            WHERE COD_IND = :cod_ind 
            ORDER BY DAT_CRE_IAE DESC 
            LIMIT 1
        ");
        $insAdmEtp = $stmt->executeQuery(['cod_ind' => $codInd])->fetchAssociative();

        // Fetch student's submissions
        $submissions = $connDefault->prepare("
            SELECT cs.id, cs.convention_id, cs.zip_file, cs.date_submission, cs.etat, c.etablissement
            FROM convention_submission cs
            LEFT JOIN conventiondd c ON cs.convention_id = c.id
            WHERE cs.etudiant_id = :etudiant_id
            ORDER BY cs.date_submission DESC
        ")->executeQuery(['etudiant_id' => $user->getId()])->fetchAllAssociative();

        $logger->debug('Submissions found: ' . json_encode($submissions));

        // Check for etat changes and add flash messages
        $lastViewedStates = $session->get('submission_states', []);
        foreach ($submissions as $submission) {
            $submissionId = $submission['id'];
            $currentEtat = $submission['etat'];
            $lastEtat = $lastViewedStates[$submissionId] ?? null;

            if ($lastEtat !== $currentEtat) {
                if ($currentEtat === 'En cours') {
                    $this->addFlash('info', 'Votre demande pour ' . $submission['etablissement'] . ' est en cours de traitement.');
                } elseif ($currentEtat === 'Présélectionné') {
                    $this->addFlash('success', 'Votre demande pour ' . $submission['etablissement'] . ' a été présélectionnée.');
                } elseif ($currentEtat === 'Admis') {
                    $this->addFlash('success', 'Félicitations ! Votre demande pour ' . $submission['etablissement'] . ' a été admise.');
                } elseif (strpos($currentEtat, 'Non Présélectionné') === 0) {
                    $motif = explode(': ', $currentEtat)[1] ?? '';
                    $this->addFlash('danger', 'Votre demande pour ' . $submission['etablissement'] . ' a été Non Présélectionné' . ($motif ? ' : ' . $motif : '.'));
                }
                $lastViewedStates[$submissionId] = $currentEtat;
            }
        }
        $session->set('submission_states', $lastViewedStates);
        $logger->debug('Updated session submission_states: ' . json_encode($lastViewedStates));

        if (!$insAdmEtp) {
            $logger->debug('No ins_adm_etp record found for COD_IND: ' . $codInd);
            $this->addFlash('info', 'Désolé, aucune inscription trouvée pour votre profil.');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
                'conventions' => [],
            ]);
        }

        $filiere = trim($insAdmEtp['COD_ETP']);
        $codAnu = $insAdmEtp['COD_ANU'];
        $datCreIae = $insAdmEtp['DAT_CRE_IAE'];
        $logger->debug('Most recent filiere: ' . $filiere . ', COD_ANU: ' . $codAnu . ', DAT_CRE_IAE: ' . $datCreIae);

        // Check if the most recent COD_ETP ends with '2' and is not IIAP*
        if (!preg_match('/2$/', $filiere) || preg_match('/^IIAP/', $filiere)) {
            $logger->debug('Invalid COD_ETP for convention: ' . $filiere . ', blocking form access');
            $this->addFlash('info', 'Désolé, aucune convention est disponible pour votre filière. Seuls les étudiants en 2ème année (ex. IITR2, IIGI2, mais pas IIAP2 ou IITR3) peuvent soumettre une demande.');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
                'conventions' => [],
            ]);
        }

        // Extract base filiere (e.g., IITR2 -> IITR)
        $baseFiliere = preg_replace('/\d+$/', '', $filiere);
        $logger->debug('Base filiere: ' . $baseFiliere);
        $currentDate = new \DateTime();

        // Fetch open conventions with message field
        $stmt = $connDefault->prepare("
            SELECT id, etablissement, message
            FROM conventiondd
            WHERE status = 'Ouverte'
            AND :current_date BETWEEN modal_datedebut AND modal_datefin
            AND JSON_CONTAINS(filiere, :base_filiere)
        ");
        $conventions = $stmt->executeQuery([
            'current_date' => $currentDate->format('Y-m-d H:i:s'),
            'base_filiere' => json_encode($baseFiliere)
        ])->fetchAllAssociative();

        $logger->debug('Conventions found with messages: ' . json_encode($conventions));

        // Validate conventions array
        foreach ($conventions as $index => $convention) {
            if (!is_array($convention) || !isset($convention['id']) || !isset($convention['etablissement'])) {
                $logger->debug('Invalid convention at index: ' . $index . ': ' . json_encode($convention));
                unset($conventions[$index]);
            }
        }

        if (empty($conventions)) {
            $logger->debug('No valid conventions found for base filiere: ' . $baseFiliere . ', current date: ' . $currentDate->format('Y-m-d H:i:s'));
            $this->addFlash('info', 'Aucune convention ouverte est disponible pour votre filière à ce jour. Revenez bientôt pour de nouvelles opportunités !');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
                'conventions' => [],
            ]);
        }

        $submission = new ConventionSubmission();
        $form = $this->createForm(ConventionSubmissionType::class, $submission, [
            'conventions' => $conventions,
        ]);

        $logger->debug('Form created, rendering template with conventions: ' . json_encode($conventions));
        
        return $this->render('convention_submission/index.html.twig', [
            'form' => $form->createView(),
            'submissions' => $submissions,
            'conventions' => $conventions,
        ]);
    }

    #[Route('/convention-submission/get-message/{id}', name: 'convention_get_message', methods: ['GET'])]
    public function getConventionMessage(int $id, Connection $conn, LoggerInterface $logger): JsonResponse
    {
        try {
            $logger->debug('Fetching message for convention ID: ' . $id);
            
            $stmt = $conn->prepare("SELECT message FROM conventiondd WHERE id = :id");
            $result = $stmt->executeQuery(['id' => $id])->fetchAssociative();
            
            $message = $result ? $result['message'] : null;
            
            $logger->debug('Message found: ' . ($message ? $message : 'NULL'));
            
            return new JsonResponse([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $logger->error('Error fetching convention message: ' . $e->getMessage());
            return new JsonResponse([
                'success' => false,
                'message' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/convention-submission/submit', name: 'convention_submission_submit', methods: ['POST'])]
    public function submit(Request $request, Security $security, EntityManagerInterface $em, LoggerInterface $logger, SessionInterface $session): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $logger->debug('No user logged in (submit)');
            $this->addFlash('error', 'Vous devez être connecté pour soumettre une demande.');
            return $this->redirectToRoute('convention_submission');
        }

        $code = $user->getCode();
        if (!$code) {
            $logger->debug('User code missing (submit)');
            $this->addFlash('error', 'Code utilisateur introuvable.');
            return $this->redirectToRoute('convention_submission');
        }

        $logger->debug('User code (submit): ' . $code . ', User ID: ' . $user->getId());

        // Fetch student's filiere
        $emDefault = $this->getDoctrine()->getManager('default');
        $connDefault = $emDefault->getConnection();
        $stmt = $connDefault->prepare("SELECT code FROM etudiants WHERE code = :code");
        $etudiant = $stmt->executeQuery(['code' => $code])->fetchAssociative();

        if (!$etudiant) {
            $logger->debug('No etudiant found for code (submit): ' . $code);
            $this->addFlash('error', 'Étudiant introuvable.');
            return $this->redirectToRoute('convention_submission');
        }

        $emEtudiant = $this->getDoctrine()->getManager('etudiant');
        $connEtudiant = $emEtudiant->getConnection();
        $stmt = $connEtudiant->prepare("SELECT COD_IND FROM individu WHERE COD_ETU = :cod_etu");
        $individu = $stmt->executeQuery(['cod_etu' => $code])->fetchAssociative();

        if (!$individu) {
            $logger->debug('No individu found for COD_ETU (submit): ' . $code);
            $this->addFlash('error', 'Aucun individu trouvé pour ce COD_ETU.');
            return $this->redirectToRoute('convention_submission');
        }

        $codInd = $individu['COD_IND'];
        $logger->debug('COD_IND (submit): ' . $codInd);

        // Fetch the most recent COD_ETP
        $stmt = $connEtudiant->prepare("
            SELECT COD_ETP, COD_ANU, DAT_CRE_IAE
            FROM ins_adm_etp 
            WHERE COD_IND = :cod_ind 
            ORDER BY DAT_CRE_IAE DESC 
            LIMIT 1
        ");
        $insAdmEtp = $stmt->executeQuery(['cod_ind' => $codInd])->fetchAssociative();

        // Fetch student's submissions
        $submissions = $connDefault->prepare("
            SELECT cs.id, cs.convention_id, cs.zip_file, cs.date_submission, cs.etat, c.etablissement
            FROM convention_submission cs
            LEFT JOIN conventiondd c ON cs.convention_id = c.id
            WHERE cs.etudiant_id = :etudiant_id
            ORDER BY cs.date_submission DESC
        ")->executeQuery(['etudiant_id' => $user->getId()])->fetchAllAssociative();

        $logger->debug('Submissions found (submit): ' . json_encode($submissions));

        if (!$insAdmEtp) {
            $logger->debug('No ins_adm_etp record found for COD_IND (submit): ' . $codInd);
            $this->addFlash('info', 'Désolé, aucune inscription trouvée pour votre profil.');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
            ]);
        }

        $filiere = trim($insAdmEtp['COD_ETP']);
        $codAnu = $insAdmEtp['COD_ANU'];
        $datCreIae = $insAdmEtp['DAT_CRE_IAE'];
        $logger->debug('Most recent filiere (submit): ' . $filiere . ', COD_ANU: ' . $codAnu . ', DAT_CRE_IAE: ' . $datCreIae);

        // Check if the most recent COD_ETP ends with '2' and is not IIAP*
        if (!preg_match('/2$/', $filiere) || preg_match('/^IIAP/', $filiere)) {
            $logger->debug('Invalid COD_ETP for convention (submit): ' . $filiere . ', blocking submission');
            $this->addFlash('info', 'Désolé, aucune convention n’est disponible pour votre filière. Seuls les étudiants en 2ème année (ex. IITR2, IIGI2, mais pas IIAP2 ou IITR3) peuvent soumettre une demande.');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
            ]);
        }

        // Extract base filiere
        $baseFiliere = preg_replace('/\d+$/', '', $filiere);
        $logger->debug('Base filiere (submit): ' . $baseFiliere);
        $currentDate = new \DateTime();

        // Fetch open conventions
        $stmt = $connDefault->prepare("
            SELECT id, etablissement
            FROM conventiondd
            WHERE status = 'Ouverte'
            AND :current_date BETWEEN modal_datedebut AND modal_datefin
            AND JSON_CONTAINS(filiere, :base_filiere)
        ");
        $conventions = $stmt->executeQuery([
            'current_date' => $currentDate->format('Y-m-d H:i:s'),
            'base_filiere' => json_encode($baseFiliere)
        ])->fetchAllAssociative();

        $logger->debug('Conventions found (submit): ' . json_encode($conventions));

        // Validate conventions array
        foreach ($conventions as $index => $convention) {
            if (!is_array($convention) || !isset($convention['id']) || !isset($convention['etablissement'])) {
                $logger->debug('Invalid convention at index (submit): ' . $index . ': ' . json_encode($convention));
                unset($conventions[$index]);
            }
        }

        if (empty($conventions)) {
            $logger->debug('No valid conventions found for base filiere (submit): ' . $baseFiliere . ', current date: ' . $currentDate->format('Y-m-d H:i:s'));
            $this->addFlash('info', 'Aucune convention ouverte n’est disponible pour votre filière à ce jour. Revenez bientôt pour de nouvelles opportunités !');
            return $this->render('convention_submission/index.html.twig', [
                'form' => null,
                'submissions' => $submissions,
            ]);
        }

        $submission = new ConventionSubmission();
        $form = $this->createForm(ConventionSubmissionType::class, $submission, [
            'conventions' => $conventions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->debug('Form submitted and valid, saving submission for user: ' . $code . ', User ID: ' . $user->getId());
            $submission->setEtudiantId($user->getId());
            $submission->setNom($user->getNom());
            $submission->setPrenom($user->getPrenom());
            $submission->setDateSubmission(new \DateTime());
            $submission->setNote2(null);
            $submission->setRemarque(null);

            // Fetch mcal from etat table using default connection (pgi_doc_db)
            try {
                $stmt = $connDefault->prepare("SELECT mcal FROM etat WHERE user_id = :user_id");
                $etat = $stmt->executeQuery(['user_id' => $user->getId()])->fetchAssociative();
                $mcal = $etat['mcal'] ?? null;
                $submission->setMcal($mcal);
                $logger->debug('Fetched mcal: ' . ($mcal ?? 'null') . ' for user_id: ' . $user->getId());
            } catch (\Exception $e) {
                $logger->error('Failed to fetch mcal from etat table for user_id: ' . $user->getId() . ', error: ' . $e->getMessage());
                // Fallback: try codetudiant
                try {
                    $stmt = $connDefault->prepare("SELECT mcal FROM etat WHERE codetudiant = :codetudiant");
                    $etat = $stmt->executeQuery(['codetudiant' => $code])->fetchAssociative();
                    $mcal = $etat['mcal'] ?? null;
                    $submission->setMcal($mcal);
                    $logger->debug('Fallback fetched mcal: ' . ($mcal ?? 'null') . ' for codetudiant: ' . $code);
                } catch (\Exception $e) {
                    $logger->error('Fallback failed to fetch mcal for codetudiant: ' . $code . ', error: ' . $e->getMessage());
                    $submission->setMcal(null);
                    $this->addFlash('warning', 'Impossible de récupérer la note mcal. La soumission sera enregistrée sans cette information.');
                }
            }

            /** @var UploadedFile $zipFile */
            $zipFile = $form->get('zipFile')->getData();
            if ($zipFile) {
                $newFilename = uniqid() . '.' . $zipFile->guessExtension();
                try {
                    $zipFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/conventions',
                        $newFilename
                    );
                    $submission->setZipFile($newFilename);
                    $logger->debug('ZIP file saved: ' . $newFilename);
                } catch (FileException $e) {
                    $logger->error('Failed to save ZIP file: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement du fichier ZIP : ' . $e->getMessage());
                    return $this->render('convention_submission/index.html.twig', [
                        'form' => $form->createView(),
                        'submissions' => $submissions,
                    ]);
                }
            }

            $em->persist($submission);
            $em->flush();

            // Update session with new submission state
            $lastViewedStates = $session->get('submission_states', []);
            $lastViewedStates[$submission->getId()] = 'En cours';
            $session->set('submission_states', $lastViewedStates);
            $logger->debug('Updated session after submission, submission_states: ' . json_encode($lastViewedStates));

            $logger->debug('Submission saved to database for user: ' . $code);
            $this->addFlash('success', 'Votre demande de convention a été soumise avec succès.');
            return $this->redirectToRoute('convention_submission');
        }

        $logger->debug('Form not submitted or invalid, re-rendering');
        return $this->render('convention_submission/index.html.twig', [
            'form' => $form->createView(),
            'submissions' => $submissions,
        ]);
    }

    #[Route('/convention-submission/delete/{id}', name: 'convention_submission_delete', methods: ['POST'])]
    public function delete(Request $request, Security $security, EntityManagerInterface $em, LoggerInterface $logger, SessionInterface $session, int $id): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $logger->error('No user logged in for delete action, submission ID: ' . $id);
            $this->addFlash('error', 'Vous devez être connecté pour supprimer une demande.');
            return $this->redirectToRoute('app_login');
        }

        $submission = $em->getRepository(ConventionSubmission::class)->findOneBy(['id' => $id, 'etudiantId' => $user->getId()]);
        if (!$submission) {
            $logger->error('Submission not found or not owned by user: ' . $id . ', user ID: ' . $user->getId());
            $this->addFlash('error', 'Demande introuvable ou vous n\'avez pas les droits pour la supprimer.');
            return $this->redirectToRoute('convention_submission');
        }

        if ($submission->getEtat() !== 'En cours') {
            $logger->error('Cannot delete submission with etat: ' . $submission->getEtat() . ', submission ID: ' . $id);
            $this->addFlash('error', 'Seules les demandes en cours peuvent être supprimées.');
            return $this->redirectToRoute('convention_submission');
        }

        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $logger->error('Invalid CSRF token for delete, submission ID: ' . $id);
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('convention_submission');
        }

        // Remove file if it exists
        if ($submission->getZipFile()) {
            $file = $this->getParameter('kernel.project_dir') . '/public/uploads/conventions/' . $submission->getZipFile();
            if (file_exists($file)) {
                try {
                    unlink($file);
                    $logger->debug('ZIP file deleted: ' . $submission->getZipFile());
                } catch (\Exception $e) {
                    $logger->error('Failed to delete ZIP file: ' . $submission->getZipFile() . ', error: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors de la suppression du fichier ZIP : ' . $e->getMessage());
                    return $this->redirectToRoute('convention_submission');
                }
            } else {
                $logger->warning('ZIP file not found: ' . $file);
            }
        }

        try {
            $em->remove($submission);
            $em->flush();
            $logger->debug('Submission deleted: ' . $id);

            // Update session to remove deleted submission state
            $lastViewedStates = $session->get('submission_states', []);
            unset($lastViewedStates[$id]);
            $session->set('submission_states', $lastViewedStates);
            $logger->debug('Updated session after deletion, submission_states: ' . json_encode($lastViewedStates));

            $this->addFlash('success', 'Votre demande de convention a été supprimée avec succès.');
        } catch (\Exception $e) {
            $logger->error('Failed to delete submission ID: ' . $id . ', error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la suppression de la demande : ' . $e->getMessage());
            return $this->redirectToRoute('convention_submission');
        }

        return $this->redirectToRoute('convention_submission');
    }




























































}









