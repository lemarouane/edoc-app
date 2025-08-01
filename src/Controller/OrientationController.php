<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as droitAcces;
use Doctrine\DBAL\Connection;
use App\Entity\Etat;
use App\Entity\ChoixOrientation;
use App\Entity\Customer\Filiere;
use App\Entity\Etudiants;
use App\Entity\AnneeUniversitaire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Twig\ConfigExtension;
class OrientationController extends AbstractController
{

    #[Route(path: '/orientation', name: 'orientation')]
    public function index(secure $security, Request $request , Connection $conn)
    {
    	$usr = $security->getUser();
        $em = $this->getDoctrine()->getManager('default');
        $em1 = $this->getDoctrine()->getManager('customer');
        $choix = $em->getRepository(ChoixOrientation::class)->findOneBy(array('user' => $usr));
        if(empty($choix)){
            $filieres=$em1->getRepository(Filiere::class)->findByCycle();
            
        }else{
            $filieres[0]['codeEtab']=$choix->getChoix1();
            $filieres[1]['codeEtab']=$choix->getChoix2();
            $filieres[2]['codeEtab']=$choix->getChoix3();
            $filieres[3]['codeEtab']=$choix->getChoix4();
            $filieres[4]['codeEtab']=$choix->getChoix5();
            $filieres[5]['codeEtab']=$choix->getChoix6();
        }
		$etat = $em->getRepository(Etat::class)->findOneBy(array('user' => $usr));
        $param= new ConfigExtension($em1); 

        $etudiant   = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn);
        $ins_Peda_E = $em->getRepository(Etudiants::class)->insPedLastByInd($etudiant["COD_IND"],$conn);

      if($ins_Peda_E[0]["COD_ETP"] != "IIAP2"){
        
        $this->get('session')->getFlashBag()->add('danger', "msg_doc_36");
        return $this->redirect($this->generateUrl('app_dashboard'));

    }else{

        return $this->render('orientation/index.html.twig', [
            'etat' => $etat,
            'filieres' => $filieres,
            'plateforme' => $param->app_config("plateforme_ouvert"),
            'fermeture' => $param->app_config('date_orientation')
        ]);
    }

       

      

    }

    #[Route(path: '/test_ap2', name: 'test_ap2')]
    public function test_ap2(secure $security, Request $request,Connection $conn)
    {
    	$em = $this->getDoctrine()->getManager('default');
        $usr = $security->getUser();

        $em1 = $this->getDoctrine()->getManager('customer');
        $param= new ConfigExtension($em1);                                                
        $anneeUniver = $em->getRepository(AnneeUniversitaire::class)->findOneBy(array('etat' => 'O'));
        $anneeUniversitaire["COD_ANU"]=$anneeUniver->getAnnee();
        $nbEtudiantsAP2 = $em->getRepository(Etudiants::class)->insAp2($usr->getCode(),$anneeUniversitaire["COD_ANU"],$param->app_config('orientation_cod_etp'),$conn);
        if(!empty($nbEtudiantsAP2)){
            return new JsonResponse("1");
        }else{
            return new JsonResponse("0");
        }
    }


    #[Route(path: '/orderChoix_{choix}', name: 'orderChoix')]
    public function choixOrder(secure $security, Request $request,Connection $conn,$choix)
    {
        $em = $this->getDoctrine()->getManager('default');
        $usr = $security->getUser();

        $em1 = $this->getDoctrine()->getManager('customer');
        $param= new ConfigExtension($em1);                                                
        $anneeUniver = $em->getRepository(AnneeUniversitaire::class)->findOneBy(array('etat' => 'O'));
        $anneeUniversitaire["COD_ANU"]=$anneeUniver->getAnnee();
        $nbEtudiantsAP2= $em->getRepository(Etudiants::class)->insAp2($usr->getCode(),$anneeUniversitaire["COD_ANU"],$param->app_config('orientation_cod_etp'),$conn);
        if($param->app_config('plateforme_ouvert') == 'true'){

            if(!empty($nbEtudiantsAP2)){

                $orderChoix  = explode(",",$choix);
                
                $filiereChoix = array("GINF", "GIND", "GSTR", "GSEA","G3EI","GCYS");
                foreach ($orderChoix as $choix) {
                    if(!in_array($choix, $filiereChoix)){ 
                        return new JsonResponse("Merci de saisir des donées corrects  !".$choix); 
                    }
                }
                
                $choix = $em->getRepository(ChoixOrientation::class)->findOneBy(array('user' => $usr));
                if(empty($choix)){
                    $choix1 =new ChoixOrientation();
                    $choix1->setChoix1($orderChoix[0]);
                    $choix1->setChoix2($orderChoix[1]);
                    $choix1->setChoix3($orderChoix[2]);
                    $choix1->setChoix4($orderChoix[3]);
                    $choix1->setChoix5($orderChoix[4]);
                    $choix1->setChoix6($orderChoix[5]);
                    $choix1->setCodeEtudiant($usr->getCode());
                    $choix1->setCCHOIX(0);
                    $choix1->setUser($usr);
                    $choix1->setAnneeUniv($anneeUniversitaire['COD_ANU']);
                    $em->persist($choix1);
                }else{

                    $choix->setChoix1($orderChoix[0]);
                    $choix->setChoix2($orderChoix[1]);
                    $choix->setChoix3($orderChoix[2]);
                    $choix->setChoix4($orderChoix[3]);
                    $choix->setChoix5($orderChoix[4]);
                    $choix->setChoix6($orderChoix[5]);
                    $choix->setCodeEtudiant($usr->getCode());
                    $choix->setCCHOIX(0);
                    $choix->setUser($usr);
                    $em->persist($choix);
                }
                $em->flush();
                return new JsonResponse("votre choix est validé !");
            }else{
                return new JsonResponse("Vous n'étes pas admis(e) en AP2  !");
            }
        }else{
            return new JsonResponse("la plateforme est fermée !");
        }
    	
    }
   

    
}
