<?php

namespace App\Controller;

use App\Entity\Etudiants;
use App\Entity\Customer\Filiere;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmploiController extends AbstractController
{

    #[Route(path: '/emploisearch', name: 'emploisearch')]
    public function emploiAction(Security $security,Connection $conn)
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array('url' => $_ENV['EDT_DATABASE_URL'].'',);
        $dbh = DriverManager::getConnection($connectionParams, $config);
        $sql="SELECT DISTINCT(ressources_etudiants.nom) AS nom ,ressources_etudiants.codeEtudiant AS codeE 
        FROM ressources_etudiants 
        LEFT JOIN ressources_groupes_etudiants USING (codeEtudiant) 
        LEFT JOIN ressources_groupes USING (codeGroupe) 
        WHERE ressources_etudiants.deleted='0' 
            AND ressources_groupes_etudiants.deleted='0' 
            AND ressources_groupes.deleted='0' 
        ORDER BY nom ASC";
        $req_groupes2=$dbh->prepare($sql);
		$req_groupes2=$req_groupes2->execute();
		$entitiesLength=$req_groupes2->fetchAll();
    	
        $weekNumber = date("W");
        $annee = date("Y");

        $groupe = array();
        $usr = $security->getUser();
        $em = $this->getDoctrine()->getManager('default');
		$em1 = $this->getDoctrine()->getManager('customer');
        $anneeUniversitaire=$em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $etudiant   = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(),$conn,$anneeUniversitaire['COD_ANU']);
        if(empty($etudiant)){
			$this->get('session')->getFlashBag()->add('danger', "msg_dash_1");
			return new RedirectResponse($this->generateUrl('app_dashboard'));
		}
        if($etudiant['COD_ETP']=='IIAP1' || $etudiant['COD_ETP']=='IIAP2'){
            $etape=substr($etudiant['COD_ETP'], 2);

            foreach($entitiesLength as $grou){
                
                if(strpos($grou['nom'],$etape) !== false){
                    array_push($groupe, $grou );
                }
            }
        }else if($etudiant['COD_DIP'] == 'IIGD'){
            foreach($entitiesLength as $grou){
                
                if(strpos($grou['nom'],'GIL') !== false){
                    array_push($groupe, $grou );
                }
            }
        }else{

            $filiere= $em1->getRepository(Filiere::class)->findOneBy(array('codeApo' => $etudiant['COD_DIP']));

            if($filiere){
				
                foreach($entitiesLength as $grou){
                
                    if(strpos($grou['nom'],$filiere->getCodeEtab()) !== false){
                        array_push($groupe, $grou );
                    }
                }

            }
        }

        return $this->render('emploi/planning.html.twig', array(
                'groupes' => $groupe,'weck' =>$weekNumber ,'annee' =>$annee));
    }

   	#[Route(path: '/textToimageedt', name: 'textToimageedt')]
	public function saveTextAsImage(Request $request,Security $security) {
		$largeur=1145-50;
		$usr = $security->getUser();
		if ($largeur<750){
			$largeur=750;
		}
		
		//recuperation de la hauteur de l ecran a laquelle on enleve 235 pour que ca rentre en hauteur dans firefox

		$hauteur=912-210;
		if ($hauteur<520){
			$hauteur=520;
		}

		// Nom de la police à utiliser
		$font = "uploads/fonts/verdana.ttf";
		$fontb = "uploads/fonts/verdanab.ttf";

		$annee = $request->request->get('annee');
		$weck = $request->request->get('weck');
		$gr = $request->request->get('groupe');

        if($annee == null || $weck ==null){
            return new Response('0');
        }
		
		// 1er Jour de la semaine
		$jour=date("w",mktime(0,0,0,1,1,$annee));
		if($jour==0){$jour=7;}
		if($jour>4){$premieran=0;}else{$premieran=-1;}
		$lundi=mktime(0,0,0,1,(($weck+$premieran)*7),$annee); 
		$jsem=date("w",$lundi);
		$lundi=mktime(0,0,0,1,(($weck+$premieran)*7)+(1-$jsem),$annee); 
		$current_day=date("Y-m-d",$lundi);

		// Pour le calcul de la durée de traitement
		$debut = explode(" ",microtime());
		$debut = $debut[1]+$debut[0];
	
		$current_student=$gr;

		//heure du début et de fin de  journée (pour 8h30 par exemple, il faut mettre 08.50)
		$heure_debut_journee=08.00;
		$heure_fin_journee=18.30;

		//heure du début et de fin de la pause de midi (pour 11h30 par exemple, il faut mettre 11.50)
		$heure_debut_pause_midi=13.27;
		$heure_fin_pause_midi=14.52;

		//heure de début et de fin de journée
		$starttime=$heure_debut_journee;
		$endtime=$heure_fin_journee;

		//heure de début et de fin de la pause de midi
		$lunchstart=$heure_debut_pause_midi;
		$lunchstop=$heure_fin_pause_midi;

		$config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array('url' => $_ENV['EDT_DATABASE_URL'].'',);
        $dbh = DriverManager::getConnection($connectionParams, $config);

			/*****************************************************************************/

			/*                                                                           */

			/*                                                                           */

			/*                         Génération trame calendrier                       */

			/*                                                                           */

			/*                                                                           */

			/*****************************************************************************/



		// [GD] On genere un png
		header ('Content-Type: image/png');

		// Largeur et hauteur des entêtes du calendrier
		$leftwidth=50;
		$topheight=50;


		// [GD] On crée l'image
		// le 75 sert pour inclure la legende sous l edt
		$im = imagecreate ($largeur, $hauteur+75) or die ("Erreur lors de la création de l'image");



		// [GD] Declaration des couleurs en RVB
		$blanc = imagecolorallocate ($im, 255, 255, 255);
		$noir = imagecolorallocate ($im, 0, 0, 0);
		$gris = imagecolorallocate ($im, 200, 200, 200);
		$grisclair = imagecolorallocate ($im, 225, 225, 225);
		$couleur_vacances=imagecolorallocate ($im, 206, 243, 187);
		$couleur_TP = imagecolorallocate ($im, 169, 252, 173);
		$couleur_TD = imagecolorallocate ($im, 249,252,169);
		$couleur_CR = imagecolorallocate ($im, 181,169, 252);
		$couleur_DS = imagecolorallocate ($im, 252, 169, 169);
		$couleur_defaut = imagecolorallocate ($im, 30, 255, 30);
		$couleur_pro = imagecolorallocate($im, 255, 200,0);
		$rdv[1] = imagecolorallocate ($im, 255, 187, 246);
		$rdv[2] = imagecolorallocate ($im, 255, 222, 132);
		$rdv[3] = imagecolorallocate ($im, 190, 132, 255);
		$rdv[4] = imagecolorallocate ($im, 255, 255, 0);
		$rdv[5] = imagecolorallocate ($im, 139, 172, 255);

		$samedi='1';

		// [GD] Création d'un polygone

		$greycadre= array(0, 0, $largeur, 0, $largeur, $topheight, $leftwidth, $topheight, $leftwidth, $hauteur-1, 0, $hauteur-1, 0, 0);

		// [GD] Mise en gris des entêtes

		imagefilledpolygon ($im, $greycadre, 7, $gris);

		// [GD] pause de midi

		if ($lunchstart>$starttime)

		imagefilledrectangle($im, $leftwidth+1 ,abs($lunchstart-$starttime)/($endtime-$starttime+0.25)*($hauteur-$topheight)+$topheight , $largeur-1, abs($lunchstop-$starttime)/($endtime-$starttime+0.25)*($hauteur-$topheight)+$topheight, $grisclair);

		// [GD] Dessin des cadres

		imageline ($im,  $leftwidth+1, $topheight, $largeur-1, $topheight, $noir);

		// [GD] On affiche les heures

		$currenttime=$starttime;

		$nbintervalles=round(($endtime-$starttime)/0.25)+1;

		for($i=0;$i<=$nbintervalles-1;$i++)
		{
			imageline ($im, $leftwidth+1, $topheight+(($hauteur-$topheight)/$nbintervalles)*$i, $largeur-1, $topheight+(($hauteur-$topheight)/$nbintervalles)*$i, $gris);
			if (!($i%2))
				imagestring ($im, 2, 10, 43+($hauteur-1-$topheight)/$nbintervalles*$i, substr(intval($currenttime)+100,-2,2).":".substr(($currenttime-intval($currenttime))*60+100,-2,2), $noir);

			$currenttime+=0.25;

		}

		// [GD] On trace les lignes, on met les jours
		if ($samedi=='0')
		{
			for($i=0;$i<=5;$i++)

				imageline ($im, round(($largeur-$leftwidth)/5*$i+$leftwidth), 0,  round(($largeur-$leftwidth)/5*$i+$leftwidth), $hauteur-1, $noir);

			imagestring ($im, 12,$leftwidth+((($largeur-$leftwidth)/5)-36)/2, 10, "Lundi", $noir);

			imagestring ($im, 12,$leftwidth+(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-36)/2, 10, "Mardi", $noir);

			imagestring ($im, 12,$leftwidth+2*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-59)/2, 10, "Mercredi", $noir);

			imagestring ($im, 12,$leftwidth+3*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-36)/2, 10, "Jeudi", $noir);

			imagestring ($im, 12,$leftwidth+4*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-59)/2, 10, "Vendredi", $noir);

			imagestring ($im, 12,$leftwidth+5*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-44)/2, 10, "Samedi", $noir);

			//largeur en pixel de la date egal 37

			imagestring ($im, 12, $leftwidth+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",$lundi), $noir);

			imagestring ($im, 12, $leftwidth+(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",strtotime("+1 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+2*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",strtotime("+2 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+3*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",strtotime("+3 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+4*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",strtotime("+4 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+5*(($largeur-$leftwidth)/5)+((($largeur-$leftwidth)/5)-37)/2, 30, date("d/m",strtotime("+5 day",$lundi)), $noir);

			$days=5;

		}else{
			for($i=0;$i<=6;$i++)
			{	
				imageline ($im, round(($largeur-$leftwidth)/6*$i+$leftwidth), 0,  round(($largeur-$leftwidth)/6*$i+$leftwidth), $hauteur-1, $noir);
					//largeur en px Lundi 36 Mardi 36 Mercredi 59 Jeudi 36  Vendredi 59  Samedi 44
			}
					

			imagestring ($im, 12,$leftwidth+((($largeur-$leftwidth)/6)-36)/2, 10, "Lundi", $noir);

			imagestring ($im, 12,$leftwidth+(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-36)/2, 10, "Mardi", $noir);

			imagestring ($im, 12,$leftwidth+2*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-59)/2, 10, "Mercredi", $noir);

			imagestring ($im, 12,$leftwidth+3*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-36)/2, 10, "Jeudi", $noir);

			imagestring ($im, 12,$leftwidth+4*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-59)/2, 10, "Vendredi", $noir);

			imagestring ($im, 12,$leftwidth+5*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-44)/2, 10, "Samedi", $noir);

			//largeur en pixel de la date egal 37	

			imagestring ($im, 12, $leftwidth+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",$lundi), $noir);

			imagestring ($im, 12, $leftwidth+(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",strtotime("+1 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+2*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",strtotime("+2 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+3*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",strtotime("+3 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+4*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",strtotime("+4 day",$lundi)), $noir);

			imagestring ($im, 12, $leftwidth+5*(($largeur-$leftwidth)/6)+((($largeur-$leftwidth)/6)-37)/2, 30, date("d/m",strtotime("+5 day",$lundi)), $noir);

			$days=6;

		}

		// [GD] Si la semaine affichée est à plus de 2 semaines de la semaine en cours on affiche "Provisoire"

		if ($lundi>strtotime("last monday +1weeks")) {
			//ratio hauteur largeur du text est de 740 en larg pour 80 de haut

			//ratio qui va bien pour la taille de la police est taille police de 60 pour valeur de la variable hauteur de 875

			$taille_police=$hauteur*60/875;

			imagettftext ($im, $taille_police, 0, ($largeur-$leftwidth-($taille_police*740/80))/2+$leftwidth , abs($lunchstart+(($lunchstop-$lunchstart)/2)-$starttime)/($endtime-$starttime+0.25)*($hauteur-$topheight)+$topheight+$taille_police/2  , $gris, $fontb, "PROVISOIRE");
		}
			
		/*****************************************************************************/

		/*                                                                           */

		/*                                                                           */

		/*                                Affichage cours                            */

		/*                                                                           */

		/*                                                                           */

		/*****************************************************************************/

		//pour chaque bdd

		// Pour tous les groupes dont l'etudiant fait partie

		$sql="SELECT *,ressources_etudiants.nom AS nom, ressources_groupes.nom AS nom_groupe FROM ressources_etudiants LEFT JOIN ressources_groupes_etudiants USING (codeEtudiant) LEFT JOIN ressources_groupes USING (codeGroupe) WHERE ressources_etudiants.codeEtudiant=:current_student AND ressources_etudiants.deleted='0' AND ressources_groupes_etudiants.deleted='0' AND ressources_groupes.deleted='0' ";
		$req_groupes2=$dbh->prepare($sql);
		$req_groupes2=$req_groupes2->execute(array(':current_student'=>$current_student));
		$res_groupe=$req_groupes2->fetchAll();
			
		$critere="AND (";
		foreach($res_groupe as $res_groupes)
		{
			$critere .= "seances_groupes.codeRessource='".$res_groupes['codeGroupe']."' OR ";	
		}
			
		$critere=substr("$critere", 0, -3);
		$critere .= ")";	
		$diffusable=1;
		// Pour les 5 ou 6 jours à afficher, on interroge la DB
		//preparation des requetes des boucles suivantes
		
		if ($diffusable==1)
		{
			$sql="SELECT *, seances.dureeSeance AS duree, seances.commentaire,matieres.couleurFond, enseignements.nom as nom_enseignement, enseignements.alias as alias_enseignement FROM seances_groupes LEFT JOIN (seances) ON (seances_groupes.codeSeance=seances.codeSeance) RIGHT JOIN (enseignements) ON (seances.codeEnseignement=enseignements.codeEnseignement)  left join matieres on (enseignements.codeMatiere=matieres.codeMatiere) WHERE matieres.deleted=0 and seances.dateSeance=:current_day AND seances.deleted=0 AND seances.diffusable=1 ".$critere." AND seances_groupes.deleted=0 AND enseignements.deleted='0'";
			$req_seance=$dbh->prepare($sql);
		}else{
			$sql="SELECT *, seances.dureeSeance, seances.commentaire,matieres.couleurFond, enseignements.nom as nom_enseignement, enseignements.alias as alias_enseignement FROM seances_groupes LEFT JOIN (seances) ON (seances_groupes.codeSeance=seances.codeSeance) RIGHT JOIN (enseignements) ON (seances.codeEnseignement=enseignements.codeEnseignement)  left join matieres on (enseignements.codeMatiere=matieres.codeMatiere) WHERE matieres.deleted=0 and seances.dateSeance=:current_day AND seances.deleted=0 AND seances.diffusable=1 ".$critere." AND seances_groupes.deleted=0 AND enseignements.deleted='0' ";
			$req_seance=$dbh->prepare($sql);
		}

        
		$sql="SELECT * FROM seances_groupes LEFT JOIN ressources_groupes ON (seances_groupes.codeRessource=ressources_groupes.codeGroupe) WHERE codeSeance=:codeSeance AND seances_groupes.deleted='0' AND ressources_groupes.deleted='0' ";
		$req_groupes3=$dbh->prepare($sql);

		$sql="SELECT * FROM seances_profs LEFT JOIN ressources_profs ON (seances_profs.codeRessource=ressources_profs.codeProf) WHERE codeSeance=:codeSeance AND seances_profs.deleted='0' AND ressources_profs.deleted='0' ";
		$req_profs=$dbh->prepare($sql);

		$sql="SELECT * FROM seances_salles LEFT JOIN ressources_salles ON (seances_salles.codeRessource=ressources_salles.codeSalle) WHERE codeSeance=:codeSeance AND seances_salles.deleted='0' AND ressources_salles.deleted='0'";
		$req_salles=$dbh->prepare($sql);

		for ($day=0;$day<$days;$day++)
		{
			$current_day=date('Y-m-d',(strtotime("+".$day." days",$lundi)));
			// On recherche pour les groupes de l'etudiant et le jour choisi l'ensemble des séances

			$rseance=$req_seance->execute(array(':current_day'=>$current_day));
			$res_seances=$rseance->fetchAll();
            //return new JsonResponse($critere . '   '.$current_day.' '."SELECT *, seances.deleted AS seance_deleted, seances.dureeSeance as duree, enseignements.nom AS nom_enseignement, seances.dateModif as date_modif_seance, seances.dateCreation as date_creation_seance, modifications.dateModif AS dateModifs, seances_profs.deleted AS seance_prof_deleted, seances_salles.deleted AS seance_salle_deleted, seances_groupes.deleted AS seance_groupe_deleted   FROM seances LEFT JOIN enseignements USING (codeEnseignement) right outer JOIN modifications on (seances.codeSeance=modifications.code) RIGHT JOIN seances_profs on (seances_profs.codeSeance=seances.codeSeance) RIGHT JOIN seances_salles on (seances_salles.codeSeance=seances.codeSeance) RIGHT JOIN seances_groupes on (seances_groupes.codeSeance=seances.codeSeance)  WHERE seances.dateSeance=:current_day     " . $critere . " and modifications.typeObjet=9   ORDER BY modifications.dateModif DESC");
			// Pour chaque séance
			foreach($res_seances as $res_seance)
			{
				// On convertit l'horaire en %age de la journée
				/* Explication conversion :
				On extrait d'une part les minutes et d'autre part l'heure.
				On transforme les minutes en fraction d'heure.
				On enlève starttime et on divise par la durée de la journée affichée endtime-starttime.
				On obtient un %age correspondant à la position du début du cours.
				Idem pour la durée mais sans enlever 8.15
				*/
				$start_time=((substr($res_seance['heureSeance'],-strlen($res_seance['heureSeance']),strlen($res_seance['heureSeance'])-2)+substr($res_seance['heureSeance'],-2,2)/60)-$starttime)/($endtime-$starttime+0.25);

				$duree=((substr($res_seance['duree'],-strlen($res_seance['duree']),strlen($res_seance['duree'])-2)+substr($res_seance['duree'],-2,2)/60))/($endtime-$starttime+0.25);

				$horaire_debut = substr((100+substr($res_seance['heureSeance'],-strlen($res_seance['heureSeance']),strlen($res_seance['heureSeance'])-2)),-2,2)."h".substr($res_seance['heureSeance'],-2,2);

				$horaire_fin = (substr($res_seance['heureSeance'],-strlen($res_seance['heureSeance']),strlen($res_seance['heureSeance'])-2)+substr($res_seance['heureSeance'],-2,2)/60) + (substr($res_seance['duree'],-strlen($res_seance['duree']),strlen($res_seance['duree'])-2)+substr($res_seance['duree'],-2,2)/60);

				$horaire_fin = substr(intval($horaire_fin + 100),-2,2)."h".substr(($horaire_fin-intval($horaire_fin))*60+100,-2,2);

				// On calcule les coordonnées du rectangle :
				$leftx = round($leftwidth + ($largeur - $leftwidth) / $days * $day); // Coté gauche

				$rightx = round($leftwidth + ($largeur - $leftwidth) / $days * ($day + 1)); // Coté droit

				$topy = $start_time * ($hauteur - $topheight) + $topheight; // Haut

				$bottomy = ($start_time + $duree) * ($hauteur - $topheight) + $topheight; // Coté gauche

				$rgroupes3= $req_groupes3->execute(array(':codeSeance'=>$res_seance['codeSeance']));
				$res_groupe3=$rgroupes3->fetchAll();

				$dechex="FFFFFF"; //couleur par défaut : blanc

				$couleur_des_seances_etudiant=0;
				// [GD] On trace la case correspondante
				//recuperation de la couleur associee au groupe ou à la matiere ou au prof et conversion en rvb
				if ($couleur_des_seances_etudiant==0) // si couleur des groupes
				{
					foreach($res_groupe3 as $res_groupes)	
					{
						$dechex=dechex($res_groupes['couleurFond']);
					}
				}elseif ($couleur_des_seances_etudiant==1) // si couleur des matieres
				{
					$dechex=dechex($res_seance['couleurFond']);
				}elseif ($couleur_des_seances_etudiant==2) // si couleur des matieres
				{
					$req_profs=$req_profs->execute(array(':codeSeance'=>$res_seance['codeSeance']));
					$res_prof=$req_profs->fetchAll();
					foreach($res_prof as $res_profs)
					{
						$dechex=dechex($res_profs['couleurFond']);
					}
					unset ($res_prof);
				}else{
					$dechex=dechex($res_groupes['couleurFond']);
				}
			
				while (strlen($dechex)<6) {

					$dechex = "0".$dechex;

				}

				//dessiner un rectangle avec les angles arrondies car gd ne le fait pas de base
				//on dessine 2 rectangle pour faire une croix et on met 4 cercles dans les coins
				//couleur
				$couleur = imagecolorallocate ($im, hexdec(substr($dechex,-2,2)), hexdec(substr($dechex,-4,2)), hexdec(substr($dechex,-6,2)));
				//valeur du rayon 15min
				//$ray=($hauteur-$topheight)/$nbintervalles;
				if ($res_seance['duree']<=100)
				{
					$ray=3;
				}else{
					$ray=5;
				}

				// dessin des 2 rectangles

				imagefilledrectangle($im,$leftx+2,$topy+$ray,$rightx-2,$bottomy-$ray,$couleur);
				imagefilledrectangle($im,$leftx+2+$ray,$topy,$rightx-2-$ray,$bottomy,$couleur);
	
				// dessin des 4 cercles

				imagefilledellipse($im, $leftx+2+$ray, $topy+$ray, $ray*2, $ray*2, $couleur);
				imagefilledellipse($im, $leftx+2+$ray, $bottomy-$ray, $ray*2, $ray*2, $couleur);
				imagefilledellipse($im, $rightx-2-$ray, $bottomy-$ray, $ray*2, $ray*2, $couleur);
				imagefilledellipse($im, $rightx-2-$ray, $topy+$ray, $ray*2, $ray*2, $couleur);
				
				imagearc($im, $leftx+2+$ray, $topy+$ray, $ray*2, $ray*2,180,270, $noir);
				imagearc($im, $leftx+2+$ray, $bottomy-$ray, $ray*2, $ray*2,90,180, $noir);
				imagearc($im, $rightx-2-$ray, $bottomy-$ray, $ray*2, $ray*2,0,90, $noir);
				imagearc($im, $rightx-2-$ray, $topy+$ray, $ray*2, $ray*2,270,0, $noir);
											
				unset($res_groupes);
				
				// On compte le nombre de lignes a afficher dans la case pour centrer par la suite le texte	
				// on met deja nbelement egal 1 pour le nom de la matiere

				$nbelements=1;
				// On compte le nombre de profs pour savoir combien il faut en afficher
				$rprofs= $req_profs->execute(array(':codeSeance'=>$res_seance['codeSeance']));
				$res_prof=$rprofs->fetchAll();	

				if (count($res_prof))
				{
					$nbelements+=count($res_prof);
				}
				$nbprof=count($res_prof);
				// On compte le nombre de salles pour savoir combien il faut en afficher

				$rsalles=$req_salles->execute(array(':codeSeance'=>$res_seance['codeSeance']));
				$res_salle=$rsalles->fetchAll();
				if (count($res_salle))
				{
					//on affiche les salles sur 1 seule ligne donc nbelement +1
					$nbelements+=1;
				}
				$nbsalle=count($res_salle);
				// On recherche pour les commentaire combien de lignes il faut afficher
				// on decoupe la chaine commantaire en fonction de la taille de la case et au niveau des espaces
				//conversion en majuscule

				$resaname=str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å','î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø','ù', 'û', 'ü', 'ú','é', 'è', 'ê', 'ë','ç', 'ÿ', 'ñ'),array('a', 'a', 'a', 'a', 'a', 'a','i', 'i', 'i', 'i','o', 'o', 'o', 'o', 'o', 'o','u', 'u', 'u', 'u','e', 'e', 'e', 'e','c', 'y', 'n'),$res_seance['commentaire']);
				$resaname=strtoupper($resaname);		
				$compteur_ligne='0';

				if($res_seance['commentaire']!="")
				{
					if (strlen($resaname)>round((($largeur-$leftwidth)/$days)*10/92))
					{
						$chaine2=substr($resaname,0,round((($largeur-$leftwidth)/$days)*10/92));
						$dernier_espace=strrpos($chaine2," ");
						if ($dernier_espace=="")
						{
							$dernier_espace=round((($largeur-$leftwidth)/$days)*10/92);								
						}

						$compteur_ligne=1;		

						while ($dernier_espace<strlen($res_seance['commentaire']) )
						{
							$chaine2=substr($resaname,$dernier_espace+1,round((($largeur-$leftwidth)/$days)*10/92));
							if (strlen($chaine2)<round((($largeur-$leftwidth)/$days)*10/92))
							{
								$dernier_espace=2000;
								$compteur_ligne+=1;

							}
							$dernier_espace2=strrpos($chaine2," ");
							//si mot vraiment trop long pour rentrer dans case on le coupe en deux
							if ($dernier_espace2=="" && $dernier_espace<1000)
							{
								$compteur_ligne=$compteur_ligne+1;
								$chaine2=substr($resaname,$dernier_espace,round((($largeur-$leftwidth)/$days)*10/92));
								$dernier_espace+=round((($largeur-$leftwidth)/$days)*10/92);
							}elseif ($dernier_espace<1000){
								$compteur_ligne=$compteur_ligne+1;
								$chaine2=substr($resaname,$dernier_espace,$dernier_espace2);
								$dernier_espace+= $dernier_espace2;												
							}			

						}
					}else{
						$compteur_ligne=1;
					}
					$nbelements+=$compteur_ligne;	

				}	
				$nom_enseignement_afficher_alias=0;
				// [GD] On affiche le type et la date des seances
				//découpage du nom de l'enseignement afin de récupérer ce qu'il y a entre les 2 premiers _
				if ($nom_enseignement_afficher_alias==1)
				{
					$cursename=explode("_",$res_seance['alias_enseignement']);
					if (!isset($cursename[1]))
					{
						$cursename[1]=$res_seance['alias_enseignement'];
					}
				}else{
					$cursename=explode("_",$res_seance['nom_enseignement']);
					if (!isset($cursename[1]))
					{
						$cursename[1]=$res_seance['nom_enseignement'];
					}
				}
							
				//affectation de la couleur au type d'enseignement
				if ($res_seance['codeTypeActivite']==2)
				{
					$couleur = $couleur_TD;
				}elseif ($res_seance['codeTypeActivite']==1){
					$couleur = $couleur_CR;
				}elseif ($res_seance['codeTypeActivite']==3){
					$couleur = $couleur_TP;
				}elseif ($res_seance['codeTypeActivite']==9){
					$couleur = $couleur_DS;

				}elseif ($res_seance['codeTypeActivite']==4){
					$couleur = $couleur_pro;
				}else{
					$couleur = $couleur_defaut;
				}

				unset($req_type);
				$sql="SELECT * FROM types_activites WHERE codeTypeActivite=:type_activite" ;
				$req_type=$dbh->prepare($sql);	
				$req_type=$req_type->execute(array(':type_activite'=>$res_seance['codeTypeActivite']));
				$res_types=$req_type->fetchAll();		
						
				foreach($res_types as $res_type)
				{
					$text = $res_type['alias']." - ";
				}				
				// [GD] On affiche les horaires			
				$size=imagettfbbox (8 , 0, $font, $text.$horaire_debut." - ".$horaire_fin);
				$box_lenght=$size[2]-$size[0];
				$box_width=$size[1]-$size[7];
				//dessin de la case de l en-tete
				// dessin des 2 rectangles
				if ($res_seance['duree']<=100)
				{
					$epaisseur=11;
				}else{
					$epaisseur=16;
				}	
				imagefilledrectangle($im,$leftx+2,$topy+$ray,$rightx-2,$topy+$epaisseur,$couleur);
				imagefilledrectangle($im,$leftx+2+$ray,$topy,$rightx-2-$ray,$topy+$epaisseur,$couleur);
				// dessin des 4 cercles

				imagefilledarc($im, $leftx+2+$ray, $topy+$ray, $ray*2, $ray*2,180,270, $couleur,IMG_ARC_EDGED);
				imagefilledarc($im, $rightx-2-$ray, $topy+$ray, $ray*2, $ray*2,270,0, $couleur,IMG_ARC_EDGED);	
				imagearc($im, $leftx+2+$ray, $topy+$ray, $ray*2, $ray*2,180,270, $noir);
				imagearc($im, $rightx-2-$ray, $topy+$ray, $ray*2, $ray*2,270,0, $noir);
				
				//ajout des traits noir qui manquent autour de la seance
				imageline($im, $leftx+2+$ray, $topy,$rightx-2-$ray, $topy, $noir);
				imageline($im, $leftx+2+$ray, $bottomy,$rightx-2-$ray, $bottomy, $noir);
				imageline($im, $leftx+2, $topy+$ray,$leftx+2, $bottomy-$ray, $noir);
				imageline($im, $rightx-2, $topy+$ray,$rightx-2, $bottomy-$ray, $noir);
				imageline($im, $leftx+2, $topy+$epaisseur,$rightx-2, $topy+$epaisseur, $noir);
				if ($res_seance['duree']<=100)
				{
					imagettftext ($im, 8, 0, $leftx + ($rightx - $leftx - $box_lenght)/2  , $topy + 10  , $noir, $font, $text.$horaire_debut." - ".$horaire_fin);
				}else{
					imagettftext ($im, 8, 0, $leftx + ($rightx - $leftx - $box_lenght)/2  , $topy + 12  , $noir, $font, $text.$horaire_debut." - ".$horaire_fin);
				}			
					
				//on affiche le nom de la seance
				//dix caracteres ont une longueur de 92px
				$cursename=substr($cursename[1],0,round((($largeur-$leftwidth)/$days)*10/92)-1);
				$size=imagettfbbox (9 , 0, $fontb, $cursename);
				$box_lenght=$size[2]-$size[0];
				$box_width=$size[1]-$size[7];
				imagettftext ($im, 9, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , ($bottomy + $topy+$epaisseur) / 2 +12 -10 * ($nbelements/2)  , $noir, $fontb, $cursename);
				$position=($bottomy + $topy+$epaisseur) / 2 +11- 10 * ($nbelements/2)+10;
				// [GD] On affiche les commentaires sur la seance
				// on decoupe la chaine commantaire en fonction de la taille de la case et au niveau des espaces
				//conversion en majuscule
				$resaname=str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å','î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø','ù', 'û', 'ü', 'ú','é', 'è', 'ê', 'ë','ç', 'ÿ', 'ñ'),array('a', 'a', 'a', 'a', 'a', 'a','i', 'i', 'i', 'i','o', 'o', 'o', 'o', 'o', 'o','u', 'u', 'u', 'u','e', 'e', 'e', 'e','c', 'y', 'n'),$res_seance['commentaire']);
				$resaname=strtoupper($resaname);
				if($res_seance['commentaire']!="")
				{
					if (strlen($resaname)>round((($largeur-$leftwidth)/$days)*10/92))
					{
						$chaine2=substr($resaname,0,round((($largeur-$leftwidth)/$days)*10/92));
						$dernier_espace=strrpos($chaine2," ");
						if ($dernier_espace=="")
						{
							$dernier_espace=round((($largeur-$leftwidth)/$days)*10/92);
							$size=imagettfbbox (7 , 0, $font, $chaine2);
							$box_lenght=$size[2]-$size[0];
							imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $chaine2);
							$position+=10;
						}else{	
							$chaine2=substr($resaname,0,$dernier_espace);
							$size=imagettfbbox (7 , 0, $font, $chaine2);
							$box_lenght=$size[2]-$size[0];											
							imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $chaine2);
							$position+=10;
						}									
						while ($dernier_espace<strlen($res_seance['commentaire']) )
						{
							$chaine2=substr($resaname,$dernier_espace,round((($largeur-$leftwidth)/$days)*10/92));
							if (strlen($chaine2)<round((($largeur-$leftwidth)/$days)*10/92))
							{
								$chaine2=substr($resaname,$dernier_espace);
								$size=imagettfbbox (7 , 0, $font, $chaine2);
								$box_lenght=$size[2]-$size[0];													
								imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $chaine2);
								$position+=10;
								$dernier_espace=2000;							
							}
							$dernier_espace2=strrpos($chaine2," ");
							//si mot vraiment trop long pour rentrer dans case on le coupe en deux
							if ($dernier_espace2=="" && $dernier_espace<1000 )
							{
								$chaine2=substr($resaname,$dernier_espace,round((($largeur-$leftwidth)/$days)*10/92));
								$dernier_espace+=round((($largeur-$leftwidth)/$days)*10/92);
								$size=imagettfbbox (7 , 0, $font, $chaine2);
								$box_lenght=$size[2]-$size[0];						
								imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $chaine2);
								$position+=10;
							}elseif ($dernier_espace<1000){
								$chaine2=substr($resaname,$dernier_espace,$dernier_espace2);
								$dernier_espace+= $dernier_espace2;
								$size=imagettfbbox (7 , 0, $font, $chaine2);
								$box_lenght=$size[2]-$size[0];						
								imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $chaine2);
								$position+=10;							
							}			
						}
					}else{
						$size=imagettfbbox (7 , 0, $font, $resaname);
						$box_lenght=$size[2]-$size[0];
						imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position , $noir, $font, $resaname);
						$position+=10;
					}
				}
				// [GD] On affiche les profs concernés
				foreach ($res_prof as $res_profs)
				{
					if ($res_profs['nom']!="")
					{	
						$size=imagettfbbox (8 , 0, $font, substr($res_profs['nom'],0,round((($largeur-$leftwidth)/$days)*10/92)-1));
						$box_lenght=$size[2]-$size[0];
						$box_width=$size[1]-$size[7];
						imagettftext ($im, 8, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position  , $noir, $font, substr($res_profs['nom'],0,round((($largeur-$leftwidth)/$days)*10/92)-1));
						$position+=10;
					}
				}				
				// [GD] On affiche les salles
				$nbsalles=0;
				unset($salles);
				$salles="";
				$nom_salle_afficher_alias=0;
				$nb_caractere_salle_pour_vue_etudiant='20';
				$affichage_mot_salle_pour_etudiant='1';
				foreach ($res_salle as $res_salles)
				{
					if ($nbsalles>0)
						$salles.=", ";
					$nbsalles++;
					if ($nom_salle_afficher_alias==1)
					{
						$salles .= substr($res_salles['alias'],0,$nb_caractere_salle_pour_vue_etudiant);
					}else{
						$salles .= substr($res_salles['nom'],0,$nb_caractere_salle_pour_vue_etudiant);
					}
						
				}					
				//affichage du mot "salle" devant la liste des salles
				if ($affichage_mot_salle_pour_etudiant=='1')
				{
					if ($nbsalles==1)
					{
						$salles="Salle : ".$salles;
					}	
					if($nbsalles>1)
					{
						$salles="Salles : ".$salles;
					}
				}
				$size=imagettfbbox (7 , 0, $font, $salles);
				$box_lenght=$size[2]-$size[0];
				$box_width=$size[1]-$size[7];
				imagettftext ($im, 7, 0, $leftx + ($rightx - $leftx - $box_lenght)/2 , $position-2, $noir, $font, $salles);
			}

		}

		// [GD] On dessine la légende
		//largeur en px de toute la legende : 558 px
		//largeur en px de toute la legende : 558 px
		imagestring($im, 10, ($largeur-558)/2, $hauteur+13, utf8_decode('Légende')." des en-tetes :", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+200, $hauteur+10, ($largeur-558)/2+200+30, $hauteur+30, $couleur_CR);
		imagerectangle($im, ($largeur-558)/2+200, $hauteur+10, ($largeur-558)/2+200+30, $hauteur+29, $noir);
		imagestring($im, 10, ($largeur-558)/2+200+40, $hauteur+13, "Cours", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+300, $hauteur+10, ($largeur-558)/2+300+30, $hauteur+30, $couleur_TD);
		imagerectangle($im, ($largeur-558)/2+300, $hauteur+10, ($largeur-558)/2+300+30, $hauteur+29, $noir);
		imagestring($im, 10, ($largeur-558)/2+300+40, $hauteur+13, "TD", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+400, $hauteur+10, ($largeur-558)/2+400+30, $hauteur+30, $couleur_TP);
		imagerectangle($im, ($largeur-558)/2+400, $hauteur+10, ($largeur-558)/2+400+30, $hauteur+29, $noir);
		imagestring($im, 10, ($largeur-558)/2+400+40, $hauteur+13, "TP", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+500, $hauteur+10, ($largeur-558)/2+500+30, $hauteur+30, $couleur_DS);
		imagerectangle($im, ($largeur-558)/2+500, $hauteur+10, ($largeur-558)/2+500+30, $hauteur+29, $noir);
		imagestring($im, 10, ($largeur-558)/2+500+40, $hauteur+13, "DS", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+200, $hauteur+35, ($largeur-558)/2+200+30, $hauteur+55, $couleur_pro);
		imagerectangle($im, ($largeur-558)/2+200, $hauteur+35, ($largeur-558)/2+200+30, $hauteur+54, $noir);
		imagestring($im, 10, ($largeur-558)/2+200+40, $hauteur+38, "Projet", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+300, $hauteur+35, ($largeur-558)/2+300+30, $hauteur+55, $couleur_defaut);
		imagerectangle($im, ($largeur-558)/2+300, $hauteur+35, ($largeur-558)/2+300+30, $hauteur+54, $noir);
		imagestring($im, 10, ($largeur-558)/2+300+40, $hauteur+38, "Autre", $noir);
		imagefilledrectangle ($im, ($largeur-558)/2+400, $hauteur+35, ($largeur-558)/2+400+30, $hauteur+55, $rdv[4]);
		imagerectangle($im, ($largeur-558)/2+400, $hauteur+35, ($largeur-558)/2+400+30, $hauteur+54, $noir);
		imagestring($im, 10, ($largeur-558)/2+400+40, $hauteur+38, utf8_decode('Réservation'), $noir);

		// [GD] On dessine un cadre autour de l'EDT
		imagerectangle ($im, 0, 0, $largeur-1, $hauteur-1, $noir);
		// Calcul du temps d'execution du script
		$fin = explode(" ",microtime());
		$fin = $fin[1]+$fin[0];
		$temps_passe = $fin-$debut;
		// [GD] Affichage durée execution
		//largeur de l affichage en pixel vaut 94px
		imagestring($im, 1, ($largeur-94)/2, $hauteur+60, utf8_decode('Généré')." le ".date("d/m/Y"). " a " .date("H:i:s"). " en ".number_format($temps_passe,3)."s" , $noir);

		$file_name = "uploads/img/".$usr->getCode().".png";
   		imagepng($im, $file_name);
		//$base64 = base64_encode(imagepng($im));
   		imagedestroy($im);
		$response = new Response($file_name);
		
		$response->headers->set('Content-Type', 'image/png');
		//$response->setStatusCode(200);
	   
		return $response;
		//return new Response($file_name);

	}
   
   
}
