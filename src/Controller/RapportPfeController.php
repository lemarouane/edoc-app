<?php

namespace App\Controller;

use App\Entity\RapportPfe;
use App\Form\RapportPfeType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RapportPfeController extends AbstractController
{
    private $pgiDocDbManager;
    private $logger;

    public function __construct(EntityManagerInterface $pgiDocDbManager, LoggerInterface $logger)
    {
        $this->pgiDocDbManager = $pgiDocDbManager;
        $this->logger = $logger;
    }

    /**
     * @Route("/etudiant/upload-rapport", name="app_etudiant_upload_rapport", methods={"GET", "POST"})
     */
    public function uploadRapport(Request $request, Security $security): Response
    {
        $rapport = new RapportPfe();
        $form = $this->createForm(RapportPfeType::class, $rapport);
        $form->handleRequest($request);

        // Initialize DBAL connections
        $config = new Configuration();
        // Get the database URL from environment variable
        $pgiDocDbUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
        $pgiDocDbConn = DriverManager::getConnection([
            'url' => $pgiDocDbUrl
        ], $config);
        // Get the database URL for pgi_ensa_db from environment variable
        $pgiEnsaDbUrl = $_ENV['CUSTOMER_DATABASE_URL'] ?? getenv('CUSTOMER_DATABASE_URL');
        $pgiEnsaDbConn = DriverManager::getConnection([
            'url' => $pgiEnsaDbUrl
        ], $config);

        $user = $security->getUser();
        if (!$user) {
            $this->logger->error('No user logged in for upload rapport');
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $code = $user->getCode();
        if (!$code) {
            $this->logger->error('User code missing for upload rapport, email: ' . $user->getEmail());
            $this->addFlash('error', 'Code utilisateur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $this->logger->debug('Processing upload for user email: ' . $user->getEmail() . ', code: ' . $code);

        // Fetch student data
        try {
            $stmt = $pgiDocDbConn->prepare("SELECT id, code, nom, prenom FROM etudiants WHERE code = :code");
            $etudiant = $stmt->executeQuery(['code' => $code])->fetchAssociative();
            $this->logger->debug('Etudiants query result: ' . json_encode($etudiant));
        } catch (\Exception $e) {
            $this->logger->error('Error querying pgi_doc_db.etudiants for code: ' . $code . ', Error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la recherche de l\'étudiant : ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }

        if (!$etudiant) {
            $this->logger->error('No etudiant found for code: ' . $code);
            $this->addFlash('error', 'Étudiant introuvable.');
            return $this->redirectToRoute('app_login');
        }

        $etudiantId = $etudiant['id'];

        // Fetch stage data
        try {
            $stmt = $pgiDocDbConn->prepare("
                SELECT id, filiere, sujet, dateDebut, dateFin
                FROM stage
                WHERE user_id = :user_id
                AND typeStage_id = 1
                AND statut = '1'
                LIMIT 1
            ");
            $stage = $stmt->executeQuery(['user_id' => $etudiantId])->fetchAssociative();
            $this->logger->debug('Stage query result: ' . json_encode($stage));
        } catch (\Exception $e) {
            $this->logger->error('Error querying pgi_doc_db.stage for user_id: ' . $etudiantId . ', Error: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la recherche du stage : ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }

        if (!$stage) {
            $this->logger->debug('No stage found for user_id: ' . $etudiantId);
            $this->addFlash('danger', 'Aucun stage actif trouvé pour votre profil.');
            return $this->redirectToRoute('app_dashboard');
        }

        $stageId = $stage['id'];
        $filiere = $stage['filiere'];

        // Fetch supervisor data
        $supervisor = null;
        try {
            $stmt = $pgiEnsaDbConn->prepare("
                SELECT encadrant
                FROM stageencad
                WHERE stage = :stage_id
                LIMIT 1
            ");
            $stageEncad = $stmt->executeQuery(['stage_id' => $stageId])->fetchAssociative();
            $this->logger->debug('Stageencad query result: ' . json_encode($stageEncad));
        } catch (\Exception $e) {
            $this->logger->error('Error querying pgi_ensa_db.stageencad for stage: ' . $stageId . ', Error: ' . $e->getMessage());
            $stageEncad = null;
        }

        if ($stageEncad && isset($stageEncad['encadrant'])) {
            $personnelId = $stageEncad['encadrant'];
            try {
                $stmt = $pgiEnsaDbConn->prepare("
                    SELECT id_user_id, nom, prenom
                    FROM personnel
                    WHERE id = :personnel_id
                ");
                $personnel = $stmt->executeQuery(['personnel_id' => $personnelId])->fetchAssociative();
                $this->logger->debug('Personnel query result: ' . json_encode($personnel));
                if ($personnel) {
                    $supervisor = [
                        'id' => $personnel['id_user_id'], // Use id_user_id (utilisateurs.id)
                        'nom' => $personnel['nom'],
                        'prenom' => $personnel['prenom'],
                        'role' => 'Encadrant',
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->error('Error querying pgi_ensa_db.personnel for personnel_id: ' . $personnelId . ', Error: ' . $e->getMessage());
            }
        }

        if (!$supervisor) {
            $baseFiliere = preg_replace('/\d+$/', '', $filiere);
            $filiereCode = 'FIL_' . $baseFiliere;
            try {
                $stmt = $pgiEnsaDbConn->prepare("
                    SELECT id
                    FROM utilisateurs
                    WHERE roles LIKE '%ROLE_CHEF_FIL%'
                    AND codes LIKE :filiere_code
                ");
                $utilisateur = $stmt->executeQuery(['filiere_code' => '%' . $filiereCode . '%'])->fetchAssociative();
                $this->logger->debug('Utilisateurs query result: ' . json_encode($utilisateur));
                if ($utilisateur) {
                    $userId = $utilisateur['id'];
                    $stmt = $pgiEnsaDbConn->prepare("
                        SELECT nom, prenom
                        FROM personnel
                        WHERE id_user_id = :user_id
                    ");
                    $personnel = $stmt->executeQuery(['user_id' => $userId])->fetchAssociative();
                    if ($personnel) {
                        $supervisor = [
                            'id' => $userId, // Use utilisateurs.id
                            'nom' => $personnel['nom'],
                            'prenom' => $personnel['prenom'],
                            'role' => 'Chef de filière',
                        ];
                        $this->logger->debug('Chef de filière details: ' . json_encode($supervisor));
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Error querying pgi_ensa_db.utilisateurs for filiere_code: ' . $filiereCode . ', Error: ' . $e->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('pdfFile')->getData();
            if ($pdfFile) {
                $newFilename = uniqid() . '.' . $pdfFile->guessExtension();
                try {
                    $pdfFile->move(
                        $this->getParameter('rapports_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->logger->error('File upload failed: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                    return $this->redirectToRoute('app_etudiant_upload_rapport');
                }

                $rapport->setFilePath($newFilename);
                $rapport->setStatus('En cours');
                $rapport->setStageId($stageId);
                $rapport->setFiliere($filiere);
                $rapport->setSujet($stage['sujet'] ?? null);
                $rapport->setDateDebut(new \DateTime($stage['dateDebut']));
                $rapport->setDateFin(new \DateTime($stage['dateFin']));
                $rapport->setEtudiantId($etudiantId);
                $rapport->setEtudiantNom($etudiant['nom']);
                $rapport->setEtudiantPrenom($etudiant['prenom']);
                $rapport->setEtudiantCode($code);
                $rapport->setEncadrantId($supervisor['id'] ?? null); // Stores utilisateurs.id
                $rapport->setEncadrantNom($supervisor['nom'] ?? null);
                $rapport->setEncadrantPrenom($supervisor['prenom'] ?? null);
                $rapport->setEncadrantRole($supervisor['role'] ?? null);
                $rapport->setCreatedAt(new \DateTimeImmutable());
                $rapport->setUpdatedAt(new \DateTimeImmutable());

                try {
                    $this->pgiDocDbManager->persist($rapport);
                    $this->pgiDocDbManager->flush();
                    $this->logger->info('Rapport uploaded successfully for user_id: ' . $etudiantId . ', file: ' . $newFilename);
                    $this->addFlash('success', 'Rapport téléchargé avec succès.');
                } catch (\Exception $e) {
                    $this->logger->error('Error persisting rapport: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement du rapport : ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_etudiant_upload_rapport');
            }
        }

        // Fetch all rapports for the current user
        $rapports = $this->pgiDocDbManager->getRepository(RapportPfe::class)->findBy([
            'etudiantId' => $etudiantId,
        ]);

        // Debug: Log and dump rapports to verify data
        $this->logger->debug('Rapports fetched for user_id: ' . $etudiantId . ', count: ' . count($rapports));
        dump($rapports); // Remove in production

        return $this->render('rapport_pfe/upload_rapport.html.twig', [
            'form' => $form->createView(),
            'rapports' => $rapports,
        ]);
    }

    /**
     * @Route("/etudiant/rapport/{id}/view", name="app_etudiant_rapport_view", methods={"GET"})
     */
    public function viewRapport(int $id): Response
    {
        $rapport = $this->pgiDocDbManager->getRepository(RapportPfe::class)->find($id);
        if (!$rapport) {
            $this->addFlash('error', 'Rapport introuvable.');
            return $this->redirectToRoute('app_etudiant_upload_rapport');
        }

        $filePath = $this->getParameter('rapports_directory') . '/' . $rapport->getFilePath();
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Fichier introuvable.');
            return $this->redirectToRoute('app_etudiant_upload_rapport');
        }

        return new Response(
            file_get_contents($filePath),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $rapport->getFilePath() . '"',
            ]
        );
    }

    /**
     * @Route("/etudiant/rapport/{id}/delete", name="app_etudiant_rapport_delete", methods={"POST"})
     */
    public function deleteRapport(Request $request, int $id): Response
    {
        $rapport = $this->pgiDocDbManager->getRepository(RapportPfe::class)->find($id);
        if (!$rapport) {
            return $this->json(['success' => false, 'message' => 'Rapport introuvable.']);
        }

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            try {
                $filePath = $this->getParameter('rapports_directory') . '/' . $rapport->getFilePath();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $this->pgiDocDbManager->remove($rapport);
                $this->pgiDocDbManager->flush();
                $this->addFlash('success', 'Rapport supprimé avec succès.');
                return $this->json(['success' => true]);
            } catch (\Exception $e) {
                $this->logger->error('Error deleting rapport: ' . $e->getMessage());
                $this->addFlash('error', 'Erreur lors de la suppression du rapport : ' . $e->getMessage());
                return $this->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.']);
    }
}
