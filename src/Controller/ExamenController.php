<?php

namespace App\Controller;

use App\Entity\Etudiants;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security ;
use App\Twig\ConfigExtension;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ExamenController extends AbstractController
{
    
    #[Route(path: '/etudiants_exam', name: 'app_exam_etudiants')]
    public function exam_user(Security $security,Connection $conn): Response
    {

        $em = $this->getDoctrine()->getManager('default');
        $em1 = $this->getDoctrine()->getManager('customer');
        $conf          = new ConfigExtension($em1);
        $exam = $em->getRepository(Etudiants::class)->getExam($conn,$conf->app_config('CENTRE_INCOMPATIBILITE'));
        $annee      = $em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $usr = $security->getUser();
        $etudiant      = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn,$annee["COD_ANU"]);
        if(empty($etudiant)){
			$this->get('session')->getFlashBag()->add('danger', "msg_exam_1");
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


        return $this->render('examen/exam.html.twig',['groupe' => $gr,'exam' => $exam,'etudiant' => $etudiant,'ins_Peda_E' => $ins_Peda_E,'ins_Adm_E' => $ins_Adm_E]);
       
    }
    #[Route(path: '/examPlanning', name: 'examPlanning')]
    public function examPlanning(Connection $conn,Request $request): Response
    {
        $periode = 'PRINT_CC';
        $em = $this->getDoctrine()->getManager('default');
        $exams = $em->getRepository(Etudiants::class)->getExamPlanning($conn,$periode);

        $stageString ="<div class='table-responsive'>
                            <table class='table table-bordered mb-0 table-striped'>
                                <thead class='bg-light-primary'>
                                    <tr>
                                        <th style='text-align:center !important ;' scope='col'>Matière</th>
                                        <th style='text-align:center !important ;' scope='col'>Date Examen</th>
                                        <th style='text-align:center !important ;' scope='col'>Heure</th>
                                        <th style='text-align:center !important ;' scope='col'>Durée d'Examen</th>
                                        <th style='text-align:center !important ;' scope='col'>Salle</th>
                                        <th style='text-align:center !important ;' scope='col'>Responsable</th>
                                        <th style='text-align:center !important ;' scope='col'>Surveillants</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                ";
        if(!empty($exams))
        {
            
            foreach ($exams as $exam) {
                $hours = intdiv($exam['DUR_EXA_EPR_PES'], 60).'H : '. ($exam['DUR_EXA_EPR_PES'] % 60).' Minute';
                $stageString .= '<tr style="text-align:center !important ;" >';
                $stageString .= '<td>'.$exam['LIB_EPR'].'</td>';
                $stageString .= '<td>'.$exam['DAT_DEB_PES'].'</td>';
                if(intval($exam['heure'])<10){
                    $stageString .= '<td>0'.$exam['heure'].'</td>';
                }else{
                    $stageString .= '<td>'.$exam['heure'].'</td>';
                }
                $stageString .= '<td>'.$hours.'</td>';
                $stageString .= '<td>'.$exam['COD_SAL'].'</td>';
                $stageString .= '<td>'.$exam['responsable'].'</td>';
                $surveillants = $em->getRepository(Etudiants::class)->getExamSurveillantBySalle($conn,$periode,$exam['COD_SAL'],$exam['COD_EPR']);
                $stageString .= '<td><ul>';
                foreach ($surveillants as $surveillant){
                    $stageString .= '<li>'.$surveillant['surveillant'].'</li>';
                }
                $stageString .= '</ul></td>';
                
                $stageString .= '</tr>';
            }
        }
        $stageString .="</tbody></table></div>";
       return new Response($stageString);

       
    }
    

}
