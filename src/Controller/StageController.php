<?php

namespace App\Controller;

use App\Entity\Etudiants;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as droitAcces;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;
use App\Entity\Stage;
use App\Form\stageType;
use Symfony\Component\HttpFoundation\RedirectResponse;
// Include JSON Response
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Twig\ConfigExtension;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class StageController extends AbstractController
{


    #[Route(path: '/conventionDemande', name: 'conventionDemande')]
    public function addAction(Request $request, secure $security,Connection $conn,MailerInterface $mailer)
    {
      
      $em = $this->getDoctrine()->getManager();
      $em1 = $this->getDoctrine()->getManager('customer');
      $conf= new ConfigExtension($em1);
        if($conf->app_config('convention_autorise')=='false'){
          $this->get('session')->getFlashBag()->add('danger', "msg_stage_1");
        return new RedirectResponse($this->generateUrl('app_dashboard'));
      }
      $usr = $security->getUser();
      $anneeUniversitaire = $em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
      $etudiant = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn,$anneeUniversitaire['COD_ANU']);
      if(empty($etudiant)){
        $this->get('session')->getFlashBag()->add('danger', "msg_stage_2");
        return new RedirectResponse($this->generateUrl('app_dashboard'));
      }
      $ins = $em->getRepository(Etudiants::class)->insAdmLastByInd($etudiant["COD_IND"],$conn,$conf->app_config('COD_CMP'),$conf->app_config('ETA_IAE'));
	    
      $stages = $em->getRepository(Stage::class)->findBy(array('user' => $usr));
      $entity = new Stage();
      $form = $this->createForm(stageType::class, $entity);
      $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 

          if(!$entity->getEntreprise()){
            $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
            return $this->render('stages/demande.html.twig', array('form' => $form->createView(),'stages' => $stages, 'page' => 'new'));
          }

            $file = $form->get('fichier')->getData();


            $meme_array = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-powerpoint" , "application/vnd.openxmlformats-officedocument.presentationml.presentation"];

            if ($file && array_search($file->getMimeType(),$meme_array)!==FALSE ) {
             $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
             // this is needed to safely include the file name as part of the URL
             $newFilename = $originalFilename .'-'.uniqid().'.'.$file->guessExtension();
 
             // Move the file to the directory where brochures are stored
             try {
                 $file->move(
                     $this->getParameter('satge'),
                     $newFilename
                 );
             } catch (FileException $e) {
                 // ... handle exception if something happens during file upload
             }
 
        
             $entity->setFichier($newFilename);
         }
         /*
         else{
           $this->get('session')->getFlashBag()->add('danger', "msg_doc_inv2");
           return $this->render('stages/demande.html.twig', array('form' => $form->createView(),'stages' => $stages, 'page' => 'new'));
         
       }
*/
            $diplome=$ins[0]['COD_DIP'];

            $entity->setDateEnvoie(new \DateTime());
            $entity->setUser($usr);
            $entity->setFiliere($ins[0]['COD_ETP']);
            $entity->setStatut('-1');
            $entity->setNiveau('0');
            $entity->setAnneeuniv($anneeUniversitaire['COD_ANU']);
            $em->persist($entity);
            $em->flush();   
            $this->get('session')->getFlashBag()->add('success', "msg_stage_3");
			
            $config = new \Doctrine\DBAL\Configuration();
		        $connectionParams = array('url' => $_ENV['CUSTOMER_DATABASE_URL'].'',);
		        $con = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
            $chefFiliere =  $em->getRepository(Etudiants::class)->chefFiliereEmail($diplome,$con);
            
            $email= $chefFiliere['email'];

            $html=$this->renderView('stages/emailinformation.html.twig',array(
              'responsable' => $chefFiliere));
            $message = (new TemplatedEmail())
              ->from(new Address('gcvre@uae.ac.ma', 'Convention'))
              ->to($email.'')
              ->subject("Notification d'une convention de stage")
              ->html($html)
              ;
            try {
                $mailer->send($message);
            } catch (TransportExceptionInterface $e) {
            
            }

            return $this->redirectToRoute('conventionDemande');

        }
          
        if ($form->isSubmitted() && !$form->isValid()) {
          $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
          return $this->render('stages/demande.html.twig', array('form' => $form->createView(),'stages' => $stages, 'page' => 'new'));
        }

        return $this->render('stages/demande.html.twig', array('form' => $form->createView(),'stages' => $stages, 'page' => 'new'));
       
    }


    #[Route(path: '/editStage/{id}', name: 'editStage')]
    public function editStageAction(Stage $entity, secure $security)
    {
        $em = $this->getDoctrine()->getManager();
        $usr = $security->getUser();
        $stages = $em->getRepository(Stage::class)->findBy(array('user' => $usr));
        if ($entity->getStatut()=='-1'){
          $form = $this->createForm(stageType::class, $entity);
          return $this->render('stages/demande.html.twig', array('page' => 'edit','form' => $form->createView(),'stage' => $entity,'stages' => $stages));

        }else{
            $this->get('session')->getFlashBag()->add('danger', "msg_stage_4");
            return $this->redirectToRoute('conventionDemande');
        }
        
    }


    #[Route(path: '/updateStage/{id}', name: 'updateStage')]
    public function updateAction(Request $request,Stage $stage, secure $security)
    {
      
      $em = $this->getDoctrine()->getManager();
      $usr = $security->getUser();
      $file1 = $stage->getFichier();
      $form = $this->createForm(stageType::class, $stage);
      $form->handleRequest($request);
      $stages = $em->getRepository(Stage::class)->findBy(array('user' => $usr));
        if ($form->isSubmitted() && $form->isValid()) { 
            if(!empty($file1)){
              if (file_exists($this->getParameter('satge').$file1)) {
                 unlink($this->getParameter('satge').$file1);
              }
              
            }
            $file = $form->get('fichier')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = $originalFilename .'-'.uniqid().'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('satge'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

           
                $stage->setFichier($newFilename);
            }
            $stage->setDateEnvoie(new \DateTime());
            $stage->setStatut('-1');
            $stage->setNiveau('0');
            $em->persist($stage);
            $em->flush();   
            $this->get('session')->getFlashBag()->add('success', "msg_stage_3");
            return $this->redirectToRoute('conventionDemande'); 
        }
          
        if ($form->isSubmitted() && !$form->isValid()) {
          $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
          return $this->render('stages/demande.html.twig', array('page' => 'edit', 'form' => $form->createView(),'stages' => $stages,'stage' => $stage));
        }

        return $this->render('stages/demande.html.twig', array('page' => 'edit','form' => $form->createView(),'stages' => $stages,'stage' => $stage));
       
    }

}
