<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Etudiants;
use Doctrine\DBAL\Connection;
use App\Twig\ConfigExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DashboardController extends AbstractController
{
    #[Route(path: '/dashboard', name: 'app_dashboard')]
    public function index(Security $security, Request $request, Connection $conn): Response
    {
        $usr = $security->getUser();

        if ($usr->getType() == 'FI') {
            // Original code for 'FI' users remains unchanged
            $em = $this->getDoctrine()->getManager('default');
            $em1 = $this->getDoctrine()->getManager('customer');
            $etudiant = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(), $conn);
            $conf = new ConfigExtension($em1);
            $ins_Adm_E = $em->getRepository(Etudiants::class)->insAdmLastByInd($etudiant["COD_IND"], $conn, $conf->app_config('COD_CMP'), $conf->app_config('ETA_IAE'));

            $ins_Peda_E = $em->getRepository(Etudiants::class)->insPedLastByInd($etudiant["COD_IND"], $conn);

            $anneeUniversitaire = $em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
            $groupe = $em->getRepository(Etudiants::class)->getGroupeByInd($etudiant["COD_IND"], $anneeUniversitaire['COD_ANU'], $conn);
            $gr = $groupe ? $groupe['COD_EXT_GPE'] : $ins_Adm_E[0]['COD_ETP'];

            $etudiant = $this->replaceNullWithDefault($etudiant);
            $ins_Adm_E = $this->replaceNullWithDefault($ins_Adm_E);
            $ins_Peda_E = $this->replaceNullWithDefault($ins_Peda_E);

            return $this->render('dashboard/dashboard.html.twig', [
                'groupe' => $gr,
                'user' => $usr,
                'etudiant' => $etudiant,
                'ins_Peda_E' => $ins_Peda_E,
                'ins_Adm_E' => $ins_Adm_E
            ]);
        } 
        else if ($usr->getType() == 'FD') {
            $em1 = $this->getDoctrine()->getManager('customer');
            $customerConn = $em1->getConnection();

            // Use default connection for pgi_doc_db
            $emDefault = $this->getDoctrine()->getManager('default');
            $defaultConn = $emDefault->getConnection();

            $cin = $usr->getCode();

            $stmt = $customerConn->prepare("
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

            $stmt = $customerConn->prepare("
                SELECT s.* 
                FROM struct_rech s
                JOIN validated_doctorants vd ON vd.structure_id = s.id
                WHERE vd.id = :validated_id
            ");
            $structure = $stmt->executeQuery(['validated_id' => $doctorant['validated_id']])->fetchAssociative();

            $stmt = $customerConn->prepare("
                SELECT p.* 
                FROM personnel p
                JOIN validated_doctorants vd ON vd.personnel_id = p.id
                WHERE vd.id = :validated_id
            ");
            $personnel = $stmt->executeQuery(['validated_id' => $doctorant['validated_id']])->fetchAssociative();

            // Fetch inscription records from pgi_doc_db
            $stmt = $defaultConn->prepare("
                SELECT id, niveau_id, niveau_intitule, annee, piece_jointe
                FROM inscription
                WHERE doctorant_id = :doctorant_id
                ORDER BY annee DESC
            ");
            $inscriptions = $stmt->executeQuery(['doctorant_id' => $doctorant['id']])->fetchAllAssociative();

            $doctorant = $this->replaceNullWithDefault($doctorant);
            $structure = $this->replaceNullWithDefault($structure);
            $personnel = $this->replaceNullWithDefault($personnel);
            $inscriptions = $this->replaceNullWithDefault($inscriptions);

            return $this->render('dashboard/doctorant_dashboard.html.twig', [
                'user' => $usr,
                'doctorant' => $doctorant,
                'structure' => $structure,
                'personnel' => $personnel,
                'inscriptions' => $inscriptions
            ]);
        }
        else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * Recursively replaces NULL values in an array with '--'.
     *
     * @param array $data The input array.
     * @return array The modified array with NULL values replaced.
     */
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
    
    
    
    #[Route(path: '/resultat', name: 'app_resultat')]
    public function resultat(Security $security, Request $request,Connection $conn)
    {
		$em = $this->getDoctrine()->getManager('default');
		$usr = $security->getUser();
        
        $em1 = $this->getDoctrine()->getManager('customer');
        $conf          = new ConfigExtension($em1);
        $etudiant      = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn);

        $ins_Adm_E  = $em->getRepository(Etudiants::class)->insAdmLastByInd($etudiant["COD_IND"],$conn,$conf->app_config('COD_CMP'),$conf->app_config('ETA_IAE'));
        
		$ins_Peda_E = $em->getRepository(Etudiants::class)->insPedLastByInd($etudiant["COD_IND"],$conn);
        $anneeUniversitaire=$em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $groupe   = $em->getRepository(Etudiants::class)->getGroupeByInd($etudiant["COD_IND"],$anneeUniversitaire['COD_ANU'],$conn);
		$gr='';
        if($groupe){
            $gr=$groupe['COD_EXT_GPE'];
        }else{
            $gr=$ins_Adm_E[0]['COD_ETP'];
        }

        $initiale = explode(",", $conf->app_config('initiale'));

		$resultats_elp = $em->getRepository(Etudiants::class)->resultat_elp($conn,$initiale,$conf->app_config('master'),$etudiant["COD_IND"]);

		$resultats_vet = $em->getRepository(Etudiants::class)->resultat_vet($conn,$etudiant["COD_IND"],$conf->app_config('typeResultat'));


		$details = $this->unique_multidim_array($resultats_elp,'COD_ELP','NOT_ELP','COD_ANU');
		$details1 = $this->unique_multidim_array($resultats_vet,'COD_ETP','NOT_VET','COD_ANU');

		$found_key = array_search('IIAP1', array_column($details1, 'COD_ETP'));
		//return new JsonResponse($details)	;
		return $this->render('dashboard/releve.html.twig', ['groupe' => $gr,'details' => $details,'details1' => $details1,'etudiant' => $etudiant,'ins_Peda_E' => $ins_Peda_E,'ins_Adm_E' => $ins_Adm_E
        ]);	
		
    }

    #[Route(path: '/resultat_annee', name: 'app_resultat_annee')]
    public function resultatAnnee(Security $security, Connection $conn)
    {
		$em = $this->getDoctrine()->getManager('default');
        $em1 = $this->getDoctrine()->getManager('customer');
		$usr = $security->getUser();
        $annee      = $em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $conf          = new ConfigExtension($em1);
        $initiale = explode(",", $conf->app_config('initiale'));
        $etudiant      = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn,$annee["COD_ANU"]);
        if(empty($etudiant)){
			$this->get('session')->getFlashBag()->add('danger', "msg_dash_1");
			return new RedirectResponse($this->generateUrl('app_dashboard'));
		}
        $ins_Adm_E  = $em->getRepository(Etudiants::class)->insAdmLastByInd($etudiant["COD_IND"],$conn,$conf->app_config('COD_CMP'),$conf->app_config('ETA_IAE'));
        
		$ins_Peda_E = $em->getRepository(Etudiants::class)->insPedLastByInd($etudiant["COD_IND"],$conn);
        $anneeUniversitaire=$em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $groupe   = $em->getRepository(Etudiants::class)->getGroupeByInd($etudiant["COD_IND"],$anneeUniversitaire['COD_ANU'],$conn);
		$gr='';
        if($groupe){
            $gr=$groupe['COD_EXT_GPE'];
        }else{
            $gr=$ins_Adm_E[0]['COD_ETP'];
        }
        $affichage =  $em->getRepository(Etudiants::class)->getEtatDelebiration($conn,$annee["COD_ANU"],$etudiant["COD_ETP"]);
		//if($affichage["ETA_AVC_VET"] != 'E'){
            $resultats_elp = $em->getRepository(Etudiants::class)->resultat_elp($conn,$initiale,$conf->app_config('master'),$etudiant["COD_IND"],$annee["COD_ANU"]);

            $resultats_vet = $em->getRepository(Etudiants::class)->resultat_vet_global($conn,$etudiant["COD_IND"],$conf->app_config('typeResultat'),$annee["COD_ANU"]);


            $details = $this->unique_multidim_array($resultats_elp,'COD_ELP','NOT_ELP','COD_ANU');
            $details1 = $this->unique_multidim_array($resultats_vet,'COD_ETP','NOT_VET','COD_ANU');

            $found_key = array_search('IIAP1', array_column($details1, 'COD_ETP'));
            //return new JsonResponse($details)	;
            return $this->render('dashboard/affichage.html.twig', ['groupe' => $gr,'details' => $details,'details1' => $details1,'etudiant' => $etudiant,'ins_Peda_E' => $ins_Peda_E,'ins_Adm_E' => $ins_Adm_E
            ]);	
       /* }else{
            $this->get('session')->getFlashBag()->add('danger', "msg_dash_2");
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }*/
		
    }


    function unique_multidim_array($array, $key,$note,$annee) {

	    $temp_array = array();

	    $i = 0;

	    $key_array = array();

	    

	    foreach($array as $val) {

	        if (in_array($val[$key], $key_array)) {

	        	$element = array_search($val[$key], $key_array);
	        	if($val[$note]>=$temp_array[$element][$note] || $val[$annee]!=$temp_array[$element][$annee]){
	        		$key_array[$element] = $val[$key];

	            	$temp_array[$element] = $val;
	        	}
	        }else{

	        	$key_array[$i] = $val[$key];

	            $temp_array[$i] = $val;
	        }

	        $i++;

	    }

	    return $temp_array;

	}


    
   

   
}
