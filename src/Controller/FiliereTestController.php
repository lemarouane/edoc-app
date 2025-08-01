<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FiliereTestController extends AbstractController
{
    #[Route('/filiere-test', name: 'filiere_test')]
    public function index(Security $security, Connection $conn): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        if (!$user) {
            return new Response('Erreur: Aucun utilisateur connecté.', 403);
        }

        // Get the user's code from the etudiants table (pgi_doc_db)
        $code = $user->getCode();

        if (!$code) {
            return new Response('Erreur: Code utilisateur introuvable.', 404);
        }

        // Use the default connection (pgi_doc_db) to fetch the code
        $emDefault = $this->getDoctrine()->getManager('default');
        $connDefault = $emDefault->getConnection();

        // Verify the code exists in the etudiants table
        $stmt = $connDefault->prepare("
            SELECT code
            FROM etudiants
            WHERE code = :code
        ");
        $etudiant = $stmt->executeQuery(['code' => $code])->fetchAssociative();

        if (!$etudiant) {
            return new Response('Erreur: Étudiant introuvable dans la table etudiants.', 404);
        }

        // Switch to etudiant connection (ensat_apo) to match COD_ETU with code and get COD_IND
        $emEtudiant = $this->getDoctrine()->getManager('etudiant');
        $connEtudiant = $emEtudiant->getConnection();

        $stmt = $connEtudiant->prepare("
            SELECT COD_IND
            FROM individu
            WHERE COD_ETU = :cod_etu
        ");
        $individu = $stmt->executeQuery(['cod_etu' => $code])->fetchAssociative();

        if (!$individu) {
            return new Response('Erreur: Aucun individu trouvé pour ce COD_ETU.', 404);
        }

        $codInd = $individu['COD_IND'];

        // Use COD_IND to fetch COD_ETP from ins_adm_etp
        $stmt = $connEtudiant->prepare("
            SELECT COD_ETP
            FROM ins_adm_etp
            WHERE COD_IND = :cod_ind
        ");
        $insAdmEtp = $stmt->executeQuery(['cod_ind' => $codInd])->fetchAssociative();

        if (!$insAdmEtp) {
            return new Response('Erreur: Aucune filière (COD_ETP) trouvée pour cet individu.', 404);
        }

        $filiere = $insAdmEtp['COD_ETP'];

        // Return the filiere for testing
        return new Response('Filière de l\'étudiant: ' . $filiere, 200);
    }
}