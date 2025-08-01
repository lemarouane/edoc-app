<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Security\Core\Security;
use Doctrine\DBAL\Connection;
use App\Entity\EtuReleveAttestation;
use App\Entity\EtuAttestation;
use App\Entity\EtuDiplomeCarte;
use App\Entity\Etudiants;
use App\Entity\image;
use App\Entity\Cvtheque;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Twig\ConfigExtension;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\CvthequeType;
use App\Repository\CvthequeRepository;
use App\Repository\ClubsRepository;
use App\Repository\FormationsRepository;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
    
class CvthequeController extends AbstractController
{

    /**
     *
     * @Security("is_granted('ROLE_USER')")
     */
  #[Route(path: '/cvtheque_new', name: 'cvtheque_new')]
    public function index(secure $security, Request $request,Connection $conn , TranslatorInterface $translator  , CvthequeRepository $CvthequeRepository , ClubsRepository $ClubsRepository , FormationsRepository $FormationsRepository): Response
    {
      
    
      if($CvthequeRepository->findOneBy( ['idUser'=> $security->getUser()->getId()] )!= null)
      {
        $em = $this->getDoctrine()->getManager();
        $cvtheque_new = $em->getRepository(Cvtheque::class)->findOneBy(['idUser' => $security->getUser()->getId()]);

      }else{

        $cvtheque_new = new Cvtheque();
        $cvtheque_new->setIdUser($security->getUser()) ;
      }
   
      $form = $this->createForm(CvthequeType::class, $cvtheque_new ); // , ['data_class'=>null]
      $form->handleRequest($request);
 
      if ($form->isSubmitted() )
      {  
        if($CvthequeRepository->findOneBy( ['idUser'=> $security->getUser()->getId()] )!= null)
        {
          $em = $this->getDoctrine()->getManager();
          $cvtheque_old = $em->getRepository(Cvtheque::class)->findOneBy(['idUser' => $security->getUser()->getId()]);
          $cvtheque_new = $cvtheque_old ;
             
          $CvthequeRepository->remove($cvtheque_old, true);
         // $em->flush();
        }
        $this->get('session')->getFlashBag()->add('success', "MOD_SUCCESS");
        $CvthequeRepository->save($cvtheque_new, true);
       
      }

      //dd($cvtheque_new);
      return $this->renderForm('cvtheque/dashboard.html.twig', ['form' => $form , 'cvtheque' =>   $cvtheque_new]);

 

    }










  /**
     *
     * @Security("is_granted('ROLE_USER')")
     */
    #[Route('/cvtheque_edit_{id}_edit', name: 'cvtheque_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cvtheque $cvtheque, secure $security , CvthequeRepository $CvthequeRepository): Response
    {
        if($cvtheque->getUserId() == $security->getUser()->getId()){

            $form = $this->createForm(Cvtheque::class, $cvtheque);
            $form->handleRequest($request);
        //    $em = $this->getDoctrine()->getManager();
          //  $laureats=$em->getRepository(Laureats::class)->findBy(["personnel"=>$security->getUser()->getPersonnel()->getId()]);

   
    
           
    
            return $this->renderForm('ordre_mission/edit-ordre-mission.html.twig', [
                'ordre_mission' => $ordreMission,
                'form' => $form,
                'ordre_missions'=> $ordreMissions,
                'id' => $ordreMission->getId(), 
            ]);





        }else{
            $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }
     
    }










    #[Route('/cvtheque_pdf_{id}', name: 'cvtheque_pdf', methods: ['GET','POST'])]
    public function pdf(secure $security, Request $request, CvthequeRepository $CvthequeRepository, $id ): Response
    {
  
    
    

      return new RedirectResponse($this->generateUrl('cvtheque'));
	//	return $this->render('laureats/dashboard.html.twig', ['form' => $form->createView()]);

    }


} 
