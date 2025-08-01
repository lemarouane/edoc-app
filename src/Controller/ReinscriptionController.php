<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security as secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as droitAcces;
use App\Entity\Reinscription;
use App\Entity\AnneeUniversitaire;
use App\Entity\EtapeAv;
use App\Entity\Etat;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\profileType;
use Doctrine\DBAL\Connection;

class ReinscriptionController extends AbstractController
{
    




    /**
     * @Route("/reinscription", name="reinscription")
     */
    public function reinscriptionAction(secure $security)
    {
        $usr = $security->getUser();
		
		//$this->get('session')->getFlashBag()->add('danger', "La ré-inscription est fermée  !");
        
        //return new RedirectResponse($this->generateUrl('dashboard'));
		$em = $this->getDoctrine()->getManager('default');
        $form = $this->createForm(profileType::class, $usr);
		$reinscriptions = $em->getRepository(Reinscription::class)->findBy(array('idUser' => $usr));
        return $this->render('reinscription/reinscription.html.twig', array('entity' => $usr, 'form' => $form->createView(),'reinscriptions' => $reinscriptions));
    }

    /**
     * @Route("/updateReinscription", name="updateReinscription")
     */
    public function updateAction(Request $request,secure $security,Connection $conn) {
        
		//$this->get('session')->getFlashBag()->add('danger', "La ré-inscription est fermée  !");
        
        //return new RedirectResponse($this->generateUrl('dashboard'));
		$em = $this->getDoctrine()->getManager('default');
        $usr = $security->getUser();

        $form = $this->createForm(profileType::class, $usr);
        $form->handleRequest($request);

        if ($form->isValid()){

                $reinscription = new Reinscription();
                                               
                $anneeUniver = $em->getRepository(AnneeUniversitaire::class)->findOneBy(array('etat' => 'O'));
                $anneeUniversitaire["COD_ANU"]=$anneeUniver->getAnnee();

                $resultatPre= $conn->fetchAssociative(" select i.COD_IND,i.COD_ETU,rv.COD_TRE,rv.COD_ETP,rv.COD_ANU
                                                from individu i ,resultat_vet rv 
                                                 where  rv.COD_ANU='".($anneeUniversitaire["COD_ANU"]-1)."'
                                                    and i.COD_ETU='".$usr->getCode()."'
                                                    and i.COD_IND=rv.COD_IND
													and rv.COD_SES='1' and rv.COD_ADm='1'
                                                    and COD_TRE is not null ");
														
                if(!empty($resultatPre)){
					$reinscriptionL = $em->getRepository(Reinscription::class)->findBy(array('idUser' => $usr,'annUnivAnc' => $anneeUniversitaire['COD_ANU']-1 , 'annNouv' => $anneeUniversitaire["COD_ANU"] ));
                    $demande = "";
					if(empty($reinscriptionL)){			
						$demande = "OK" ;
					}else{
						foreach ($reinscriptionL as $value) {
							if($value->getStatut()== "refuser"){
								$demande = "OK" ;
							}else{
								$demande = "KO" ;
							}
						}
						/*
						if($reinscriptionL[0]->getStatut()== "refuser"){
							$demande = "OK" ;
						}else{
							$demande = "KO" ;
						}
						*/
					}
					
					if($demande=="OK"){
						
						$reinscription->setDateDemande(new \DateTime('now'));
						$reinscription->setAnnUnivAnc($anneeUniversitaire['COD_ANU']-1);
						$reinscription->setEtapeAnc($resultatPre['COD_ETP']);
						$reinscription->setAnnNouv($anneeUniversitaire["COD_ANU"]);

						$etapeSuivante="";
						
						if($resultatPre['COD_TRE']=='AJ' || $resultatPre['COD_TRE'] == 'ABL' || $resultatPre['COD_TRE'] == 'ROR'){
							$etapeSuivante= $resultatPre['COD_ETP'];
						}else if($resultatPre['COD_TRE']=='ADM' || $resultatPre['COD_TRE'] == 'ADMR'){
							if($resultatPre['COD_ETP']=='IIAP2'){
								$etat= $em->getRepository(Etat::class)->findOneBy(array("anneeuniv" => ($anneeUniversitaire["COD_ANU"]-1),"user" => $usr->getId()));
								$etapeSuivante = $etat->getChoixaffecter()->getAffectation();
							}else{
								$etapeAv= $em->getRepository(EtapeAV::class)->findOneBy(array("etapeAnc" => $resultatPre['COD_ETP']));
								$etapeSuivante = $etapeAv->getEtapeNouv();
							}
						}
						$reinscription->setEtapeNouv($etapeSuivante);
						$reinscription->setResultat($resultatPre['COD_TRE']);
						$reinscription->setStatut('-1');
						
						$usr->getImage()->manualRemove($usr->getImage()->getAbsolutePath());
						$usr->getImage()->upload();
						$usr->addReinscription($reinscription);
						$em->persist($usr);
						$em->flush();
						$this->get('session')->getFlashBag()->add('success', "MOD_SUCCESS");
						return new RedirectResponse($this->generateUrl('reinscription'));
					}else{
						$this->get('session')->getFlashBag()->add('danger', "msg_reinsc_1");
						return new RedirectResponse($this->generateUrl('reinscription'));
					}
                }else{
                    $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
        
                    return new RedirectResponse($this->generateUrl('reinscription'));
                }
            
        }

        $this->get('session')->getFlashBag()->add('danger', "MOD_DANGER");
        
        return new RedirectResponse($this->generateUrl('reinscription'));
    }






}
