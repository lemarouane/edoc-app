<?php

namespace App\Controller;

use App\Entity\AttestationInscription;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AttestationInscriptionController extends AbstractController
{
    #[Route('/attestation-inscription', name: 'attestation_list')]
    public function index(Security $security, Connection $conn, EntityManagerInterface $emCustomer): Response
    {
        $usr = $security->getUser();
    
        // Ensure we're using the customer database for 'FD' type users to fetch doctorant data
        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
    
        // Get the CIN from the user
        $cin = $usr->getCode();
    
        // Find the doctorant using the CIN from the customer database, including validated_doctorants
        $stmt = $connCustomer->prepare("
            SELECT d.*, vd.id as validated_id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorant = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();
    
        if (!$doctorant) {
            $this->addFlash('error', 'Données du doctorant introuvables.');
            return $this->redirectToRoute('app_login');
        }
    
        // Use default database to fetch attestation_inscription data
        $emDefault = $this->getDoctrine()->getManager('default');
        $connDefault = $emDefault->getConnection();
    
        // Retrieve the latest attestation request for this doctorant from the default database
        $stmt = $connDefault->prepare("
            SELECT id, nom, prenom, date_demande, etat 
            FROM attestation_inscription 
            WHERE doctorant_id = :doctorant_id 
            ORDER BY date_demande DESC 
            LIMIT 1
        ");
        $latestRequest = $stmt->executeQuery(['doctorant_id' => $doctorant['id']])->fetchAssociative();
    
        // Determine if the user can submit a new request
        $canRequestNew = true;
    
        if ($latestRequest) {
            // Check if the last request was 'Validée'
            if (strpos($latestRequest['etat'], 'Validée') !== false) {
                // Calculate the difference in days between the current date and the validation date
                $dateValidation = new \DateTime($latestRequest['date_demande']);
                $currentDate = new \DateTime();
                $interval = $currentDate->diff($dateValidation);
                $daysDifference = $interval->days;
    
                // If the difference is less than 90 days, prevent the new request
                if ($daysDifference < 90) {
                    $canRequestNew = false; // No new request can be made
                }
            } elseif (strpos($latestRequest['etat'], 'Refusée') !== false) {
                // If the request was rejected, allow the user to make a new request
                $canRequestNew = true;
            } elseif (strpos($latestRequest['etat'], 'En cours') !== false) {
                // If the request is still in progress, hide the request button
                $canRequestNew = false;
            }
        }
    
        return $this->render('attestation_inscription/index.html.twig', [
            'latestRequest' => $latestRequest,
            'canRequestNew' => $canRequestNew,
            'doctorant' => $doctorant
        ]);
    }
    
      

    #[Route('/attestation-inscription/request', name: 'attestation_request', methods: ['POST'])]
    public function requestAttestation(Security $security, Connection $conn, EntityManagerInterface $emCustomer): Response
    {
        $usr = $security->getUser();
    
        // Ensure we're using the customer database to fetch doctorant data
        $emCustomer = $this->getDoctrine()->getManager('customer');
        $connCustomer = $emCustomer->getConnection();
    
        // Get the CIN from the user
        $cin = $usr->getCode();
    
        // Fetch the doctorant's ID from the customer database, including validated_doctorants
        $stmt = $connCustomer->prepare("
            SELECT d.*, vd.id as validated_id 
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        $doctorantData = $stmt->executeQuery(['cin' => $cin])->fetchAssociative();
    
        if (!$doctorantData) {
            $this->addFlash('error', 'Données du doctorant introuvables.');
            return $this->redirectToRoute('attestation_list');
        }
    
        $doctorantId = $doctorantData['id'];
        $anneeUniv = substr($doctorantData['date_envoi'], 0, 4); // Extract only the year
    
        // Fetch the latest request from the database
        $emDefault = $this->getDoctrine()->getManager('default');
        $existingRequest = $emDefault->getRepository(AttestationInscription::class)
            ->findOneBy(['doctorantId' => $doctorantId], ['dateDemande' => 'DESC']);
    
        // Check if the user can request a new attestation
        $canRequestNew = true;
    
        if ($existingRequest && strpos($existingRequest->getEtat(), 'En cours') !== false) {
            $this->addFlash('warning', 'Vous avez déjà une demande en cours de traitement. Veuillez patienter.');
            return $this->redirectToRoute('attestation_list');
        }
    
        // If the request was rejected, allow the user to make a new request
        if ($existingRequest && strpos($existingRequest->getEtat(), 'Refusée') !== false) {
            $canRequestNew = true;
        }
    
        // Check the 3-month waiting period for valid requests
        if ($existingRequest && strpos($existingRequest->getEtat(), 'Validée') !== false) {
            $dateValidation = $existingRequest->getDateDemande();
            $currentDate = new \DateTime();
            $interval = $currentDate->diff($dateValidation);
            $daysDifference = $interval->days;
    
            if ($daysDifference < 90) {
                $canRequestNew = false;
                $this->addFlash('danger', 'Vous devez attendre 3 mois après la validation de votre dernière demande.');
                return $this->redirectToRoute('attestation_list');
            }
        }
    
        // If the request is allowed, proceed with the submission
        if ($canRequestNew) {
            $attestation = new AttestationInscription();
            $attestation->setDoctorantId($doctorantId);
            $attestation->setNom($usr->getNom());
            $attestation->setPrenom($usr->getPrenom());
            $attestation->setDateDemande(new \DateTime());
            $attestation->setEtat('En cours');
            $attestation->setAnneeUniv($anneeUniv);
    
            // Persist the attestation request
            $emDefault->persist($attestation);
            $emDefault->flush();
    
            $this->addFlash('success', 'Votre demande d\'attestation a été soumise avec succès.');
        }
    
        return $this->redirectToRoute('attestation_list');
    }
    
}