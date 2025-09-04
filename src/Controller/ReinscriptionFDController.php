<?php

namespace App\Controller;

use App\Entity\ReinscriptionDetails;
use App\Entity\FormationComplementaire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class ReinscriptionFDController extends AbstractController
{
    #[Route('/reinscription_fd', name: 'reinscription_fd', methods: ['GET', 'POST'])]
    public function reinscriptionFD(
        Request $request,
        Security $security,
        EntityManagerInterface $defaultEm,
        Pdf $knpSnappyPdf
    ): Response {
        $usr = $security->getUser();

        // Restrict to FD (Doctorant) users
        if ($usr->getType() !== 'FD') {
            $this->addFlash('danger', 'Accès réservé aux doctorants.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Use the 'customer' entity manager to access the customer database
        $customerEm = $this->getDoctrine()->getManager('customer');
        $conn = $customerEm->getConnection();

        // Get the CIN from the user code
        $cin = $usr->getCode();

        // Fetch doctorant data from the customer database
        $stmt = $conn->prepare("
            SELECT d.*, vd.id as validated_id, vd.structure_id, vd.personnel_id
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();

        if (!$doctorant) {
            $this->addFlash('danger', 'Données du doctorant introuvables ou non validées.');
            return $this->redirectToRoute('app_dashboard');
        }

        // Fetch structure data (libelle_structure) from struct_rech
        $structureStmt = $conn->prepare("
            SELECT libelle_structure 
            FROM struct_rech 
            WHERE id = :structure_id
        ");
        $structure = $structureStmt->executeQuery(['structure_id' => $doctorant['structure_id']])->fetchAssociative();
        $libelleStructure = $structure['libelle_structure'] ?? '--';

        // Prepend 0 to telephone if it exists and isn’t '--'
        if (!empty($doctorant['telephone']) && $doctorant['telephone'] !== '--') {
            $doctorant['telephone'] = '0' . $doctorant['telephone'];
        }

        // Replace NULL values with '--'
        $doctorant = $this->replaceNullWithDefault($doctorant);

        // Fetch Directeur de thèse from personnel table
        $personnelStmt = $conn->prepare("
            SELECT nom, prenom
            FROM personnel
            WHERE id = :personnel_id
        ");
        $personnel = $personnelStmt->executeQuery(['personnel_id' => $doctorant['personnel_id']])->fetchAssociative();
        $directeurTheseNomPrenom = $personnel ? $personnel['nom'] . ' ' . $personnel['prenom'] : '--';

        // Fetch Responsable de la structure d'accueil
        $structureId = $doctorant['structure_id'] ?? null;
        $responsableStructureNomPrenom = '--';
        if ($structureId) {
            $utilisateurStmt = $conn->prepare("
                SELECT u.id 
                FROM utilisateurs u
                WHERE JSON_CONTAINS(u.codes, :structure_code)
            ");
            $utilisateur = $utilisateurStmt->executeQuery(['structure_code' => '"STR_' . $structureId . '"'])->fetchAssociative();

            if ($utilisateur) {
                $responsablePersonStmt = $conn->prepare("
                    SELECT p.nom, p.prenom
                    FROM personnel p
                    WHERE p.id_user_id = :user_id
                ");
                $responsablePersonnel = $responsablePersonStmt->executeQuery(['user_id' => $utilisateur['id']])->fetchAssociative();
                $responsableStructureNomPrenom = $responsablePersonnel ? $responsablePersonnel['nom'] . ' ' . $responsablePersonnel['prenom'] : '--';
            }
        }

        // Fetch all ReinscriptionDetails for the doctorant for pre-filling and status display
        $reinscriptions = $defaultEm->getRepository(ReinscriptionDetails::class)->findBy(
            ['doctorantId' => $doctorant['id']],
            ['id' => 'DESC']
        );
        $latestReinscription = !empty($reinscriptions) ? $reinscriptions[0] : null;

        // Handle form submission
        $reinscriptionDetails = new ReinscriptionDetails();
        if ($request->isMethod('POST')) {
            // Set doctorant info
            $reinscriptionDetails->setDoctorantId($doctorant['id']);
            $reinscriptionDetails->setDoctorantFullName($doctorant['nom'] . ' ' . $doctorant['prenom']);
            $reinscriptionDetails->setStatut('En cours');

            // Page 2: Rapport d'Avancement de Thèse
            $reinscriptionDetails->setDiscipline($request->request->get('discipline'));
            $reinscriptionDetails->setSpecialite($request->request->get('specialite'));
            $reinscriptionDetails->setIntituleThese($request->request->get('intitule_these'));
            $reinscriptionDetails->setIntroduction($request->request->get('introduction'));
            $reinscriptionDetails->setProblematique($request->request->get('problematique'));
            $reinscriptionDetails->setMethodologie($request->request->get('methodologie'));
            $reinscriptionDetails->setResultats($request->request->get('resultats'));
            $reinscriptionDetails->setConclusion($request->request->get('conclusion'));
            $reinscriptionDetails->setTravauxEnAttente($request->request->get('travaux_en_attente'));

            // Page 3: Financement de la thèse
            $reinscriptionDetails->setBourseMerite($request->request->has('bourse_merite'));
            $reinscriptionDetails->setBourseMeriteDepuis($request->request->get('bourse_merite_depuis') ? new \DateTime($request->request->get('bourse_merite_depuis')) : null);
            $reinscriptionDetails->setBourseTroisiemeCycle($request->request->has('bourse_troisieme_cycle'));
            $reinscriptionDetails->setBourseTroisiemeCycleDepuis($request->request->get('bourse_troisieme_cycle_depuis') ? new \DateTime($request->request->get('bourse_troisieme_cycle_depuis')) : null);
            $reinscriptionDetails->setBourseCotutelle($request->request->has('bourse_cotutelle'));
            $reinscriptionDetails->setBourseCotutelleDateDebut($request->request->get('bourse_cotutelle_date_debut') ? new \DateTime($request->request->get('bourse_cotutelle_date_debut')) : null);
            $reinscriptionDetails->setBourseCotutelleDateFin($request->request->get('bourse_cotutelle_date_fin') ? new \DateTime($request->request->get('bourse_cotutelle_date_fin')) : null);
            $reinscriptionDetails->setBourseEchange($request->request->get('bourse_echange'));
            $reinscriptionDetails->setBourseProjetRecherche($request->request->get('bourse_projet_recherche'));
            $reinscriptionDetails->setSalarieFonction($request->request->get('salarie_fonction'));
            $reinscriptionDetails->setSalarieOrganisme($request->request->get('salarie_organisme'));
            $reinscriptionDetails->setFonctionnaireFonction($request->request->get('fonctionnaire_fonction'));
            $reinscriptionDetails->setFonctionnaireOrganisme($request->request->get('fonctionnaire_organisme'));
            $reinscriptionDetails->setCotutelle($request->request->has('cotutelle'));
            $reinscriptionDetails->setCotutelleUniversite($request->request->get('cotutelle_universite'));
            $reinscriptionDetails->setCotutelleNomPrenom($request->request->get('cotutelle_nom_prenom'));
            $reinscriptionDetails->setCotutelleTelephone($request->request->get('cotutelle_telephone'));
            $reinscriptionDetails->setCotutelleEmail($request->request->get('cotutelle_email'));

            // Page 4: Formations Complémentaires
            $formations = $request->request->get('formations', []);
            foreach ($formations as $formationData) {
                $formation = new FormationComplementaire();
                $formation->setDate($formationData['date'] ? new \DateTime($formationData['date']) : null);
                $formation->setDuree($formationData['duree']);
                $formation->setIntitule($formationData['intitule']);
                $formation->setOrganisateur($formationData['organisateur']);
                $formation->setEquivalenceHeures($formationData['equivalence_heures']);
                $reinscriptionDetails->addFormationComplementaire($formation);
            }

            // Save to database
            $defaultEm->persist($reinscriptionDetails);
            $defaultEm->flush();

            // Generate PDF with KnpSnappyPdf
            $html1 = $this->renderView('reinscription_fd/pdf_template_fd.html.twig', [
                'doctorant' => $doctorant,
                'directeur_these_nom_prenom_fd' => $directeurTheseNomPrenom,
                'libelle_structure' => $libelleStructure,
                'responsable_structure_nom_prenom' => $responsableStructureNomPrenom,
            ]);

            $html2 = $this->renderView('reinscription_fd/rapport_avancement_these.html.twig', [
                'doctorant' => $doctorant,
                'details' => $reinscriptionDetails,
            ]);

            $html3 = $this->renderView('reinscription_fd/financement_these.html.twig', [
                'doctorant' => $doctorant,
                'details' => $reinscriptionDetails,
            ]);

            $html4 = $this->renderView('reinscription_fd/formations_complementaires.html.twig', [
                'doctorant' => $doctorant,
                'formations' => $reinscriptionDetails->getFormationsComplementaires(),
            ]);

            // Combine all HTML pages into one PDF with page breaks
            $html = <<<EOD
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Réinscription FD - Université Abdelmalek Essaadi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.85;
            letter-spacing: 1px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-break {
            page-break-before: always;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title-box {
            border: 5px solid black;
            padding: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 16px;
        }
        .main-title {
            text-align: center;
            margin: 0;
            font-size: 30px;
        }
        .year {
            text-align: center;
            color: blue;
            margin-top: -5px;
            margin-bottom: 10px;
        }
        .section {
            margin: 10px 0;
        }
        .section-header {
            background: #d3d3d3;
            padding: 5px;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .form-line {
            display: flex;
            align-items: center;
            margin: 3px 0;
        }
        .form-line span.label {
            width: 220px;
            text-align: left;
        }
        .form-line span.value {
            flex: 1;
            text-align: center;
            margin-left: 10px;
            margin-right: 220px;
            padding-left: 10px;
            padding-right: 10px;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 10px;
        }
        .signature-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .signature-line span.label {
            white-space: nowrap;
        }
        .signature-line span.value {
            flex: 1;
            text-align: center;
            margin-left: 10px;
            margin-right: 220px;
            padding-left: 10px;
            padding-right: 10px;
            font-weight: bold;
        }
        .admin-section {
            border: 2px solid black;
            margin-top: 10px;
            padding: 5px;
        }
        .title {
            color: navy;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            text-align: center;
        }
        .subtitle {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-top: 5px;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-line {
            display: flex;
            gap: 10px;
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        .dotted-line {
            flex: 1;
            height: 1.2em;
            margin: 0 5px;
        }
        .year-work {
            color: navy;
            font-weight: bold;
            margin: 20px 0;
        }
        .section-title {
            font-weight: bold;
            margin: 10px 0;
        }
        .content-area {
            width: 100%;
            min-height: 100px;
            border: none;
            margin: 10px 0;
        }
        .future-work {
            color: navy;
            font-weight: bold;
            margin-top: 40px;
        }
        .subsection {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 5px 0;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .indent {
            margin-left: 20px;
        }
        .employment-section {
            margin: 15px 0;
        }
        .employment-title {
            font-weight: bold;
            margin: 10px 0;
        }
        .co-director-section {
            margin-top: 30px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 30px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            font-size: 12px;
            min-height: 30px;
        }
        th {
            background-color: #f8f8f8;
            font-weight: normal;
        }
        .table-row {
            height: 30px;
        }
        .notice {
            margin-top: 20px;
        }
        .notice-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .important-notice {
            border: 1px solid black;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <!-- Page 1: Demande de Réinscription -->
    <div class="container">
        $html1
    </div>

    <!-- Page 2: Rapport d'Avancement de Thèse -->
    <div class="container">
        $html2
    </div>

    <!-- Page 3: Financement de la thèse -->
    <div class="container page-break">
        $html3
    </div>

    <!-- Page 4: Formations Complémentaires -->
    <div class="container page-break">
        $html4
    </div>
</body>
</html>
EOD;

            // Set filename and directory path
            $filename = sha1(uniqid(mt_rand(), true)) . '.pdf';
            $dir = $this->getParameter('kernel.project_dir') . '/public/uploads/Reinscription_fd/' . $doctorant['nom'] . '_' . $doctorant['prenom'] . '/ReinscriptionFD/';
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            // Generate the PDF file and save it
            $knpSnappyPdf->generateFromHtml($html, $dir . $filename, []);

            // Save the PDF path to the ReinscriptionDetails entity
            $relativePath = $doctorant['nom'] . '_' . $doctorant['prenom'] . '/ReinscriptionFD/' . $filename;
            $reinscriptionDetails->setPdfPath($relativePath);
            $defaultEm->persist($reinscriptionDetails);
            $defaultEm->flush();

            // Stream the PDF for download
            return new PdfResponse(
                $knpSnappyPdf->getOutputFromHtml($html),
                'Demande_ReinscriptionFD_' . $doctorant['nom'] . '_' . $doctorant['prenom'] . '.pdf'
            );
        }

        // Render the form with pre-filled data if available
        return $this->render('reinscription_fd/form_fd.html.twig', [
            'reinscriptions' => $reinscriptions,
            'latest_reinscription' => $latestReinscription,
            'doctorant' => $doctorant,
        ]);
    }

    #[Route('/admin/reinscriptions', name: 'admin_reinscriptions', methods: ['GET'])]
    public function adminReinscriptions(
        #[Autowired(property: 'etudiant')] EntityManagerInterface $etudiantEntityManager,
        #[Autowired(property: 'customer')] EntityManagerInterface $customerEntityManager
    ): Response {
        // Restrict access to admin users (e.g., ROLE_ADMIN)
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $this->addFlash('error', 'Accès réservé aux administrateurs.');
            return $this->redirectToRoute('app_dashboard');
        }

        $reinscriptions = [];
        $doctorants = [];

        try {
            // Fetch reinscription details from the etudiant database
            $etudiantConnection = $etudiantEntityManager->getConnection();
            $reinscriptionsQuery = "SELECT id, doctorant_id, doctorant_full_name, discipline, specialite, intitule_these, statut, pdf_path FROM reinscription_details";
            $reinscriptions = $etudiantConnection->executeQuery($reinscriptionsQuery)->fetchAllAssociative();

            // Fetch all doctorants from the customer database
            $customerConnection = $customerEntityManager->getConnection();
            $doctorantsQuery = "SELECT id, nom, prenom, cin, telephone, email FROM doctorants";
            $doctorantsResult = $customerConnection->executeQuery($doctorantsQuery)->fetchAllAssociative();

            // Create a lookup array for doctorants by ID
            $doctorants = [];
            foreach ($doctorantsResult as $doctorant) {
                $doctorants[$doctorant['id']] = $doctorant;
            }

            // Combine data and generate PDF URLs
            foreach ($reinscriptions as &$reinscription) {
                $doctorantId = $reinscription['doctorant_id'];
                $reinscription['doctorant'] = $doctorants[$doctorantId] ?? [
                    'nom' => 'Inconnu',
                    'prenom' => '',
                    'cin' => '--',
                    'telephone' => '--',
                    'email' => '--'
                ];

                // Generate PDF URL if pdf_path exists
                if (!empty($reinscription['pdf_path'])) {
                    $reinscription['pdf_url'] = 'http://localhost/e-doc/public/uploads/Reinscription_fd/' . $reinscription['pdf_path'];
                } else {
                    $reinscription['pdf_url'] = null;
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération des réinscriptions: ' . $e->getMessage());
            $reinscriptions = [];
        }

        return $this->render('admin/reinscriptions.html.twig', [
            'reinscriptions' => $reinscriptions
        ]);
    }

    private function replaceNullWithDefault(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceNullWithDefault($value);
            } else {
                $data[$key] = $value ?? '--';
            }
        }
        return $data;
    }
}