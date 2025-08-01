<?php

namespace App\Repository;

use App\Entity\Etudiants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Etudiants>
 *
 * @method Etudiants|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiants|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiants[]    findAll()
 * @method Etudiants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantsRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiants::class);
        
    }

    public function save(Etudiants $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Etudiants $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Etudiants) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

 


    public function etudiantByInd($code,$cn,$annee=null) {

        $query="SELECT * FROM  individu i,adresse a,ins_adm_etp etp WHERE i.COD_ETU='".$code."' and i.COD_IND = a.COD_IND  and i.COD_IND = etp.COD_IND AND etp.COD_ANU LIKE '".$annee."%'";
        $result= $cn->fetchAssociative($query);
          
        return  $result ; 

    }
    public function insAdmLastByInd($code,$cn ,$cmp,$etat) {

        $query="SELECT * FROM ins_adm_etp ie, annee_uni a WHERE  ie.COD_IND='".$code."'  and ie.COD_CMP= '".$cmp."' and ie.COD_ANU=a.COD_ANU and ie.ETA_IAE='".$etat."' order by ie.COD_ANU desc";
        $result= $cn->fetchAllAssociative($query);
        return  $result ; 

    }
    public function insPedLastByInd($code,$cn) {

        $query="SELECT * FROM ins_pedagogi_etp ie, annee_uni a, etape e WHERE ie.COD_ANU=a.COD_ANU and ie.COD_ETP= e.COD_ETP and ie.COD_IND='".$code."'  order by ie.COD_ANU desc";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function getAnneeUnivEncours($cn) {

        $query="SELECT * FROM annee_uni WHERE ETA_ANU_IAE='O'";
        $result= $cn->fetchAssociative($query); 
        return  $result ; 

    }


    public function resultat_elp($cn,$initiale,$master,$code,$annee=null) {

        $query="SELECT DISTINCT(resultat_elp.COD_ELP), resultat_elp.COD_ANU, resultat_elp.COD_SES, resultat_elp.NOT_ELP,resultat_elp.NOT_PNT_JUR_ELP, resultat_elp.BAR_NOT_ELP, resultat_elp.COD_TRE, element_pedagogi.LIB_ELP, element_pedagogi.COD_NEL, typ_resultat.LIC_TRE,ins_pedagogi_etp.COD_ETP ,gr.ETA_AVC_ELP 
                FROM resultat_elp LEFT OUTER JOIN ins_pedagogi_etp  ON (resultat_elp.COD_IND=ins_pedagogi_etp.COD_IND AND resultat_elp.COD_ANU=ins_pedagogi_etp.COD_ANU ) 
                    JOIN element_pedagogi ON (resultat_elp.COD_ELP=element_pedagogi.COD_ELP)
                    LEFT OUTER JOIN typ_resultat ON (resultat_elp.COD_TRE=typ_resultat.COD_TRE) LEFT JOIN grp_resultat_elp gr ON ( resultat_elp.COD_ELP = gr.COD_ELP )
                WHERE ((  resultat_elp.COD_ELP LIKE '".$master."%' OR" ;
        foreach($initiale as $init){
                
                $query .= "  resultat_elp.COD_ELP  LIKE '".$init."%' OR";
                
        }
        $query=substr($query, 0, -2);
        $query .= " ) AND element_pedagogi.COD_NEL NOT LIKE 'AN%'  AND resultat_elp.COD_ELP NOT LIKE '%000%'
            AND ins_pedagogi_etp.COD_IND='".$code."'  AND resultat_elp.COD_ANU LIKE '".$annee."%' 
            AND  resultat_elp.COD_SES =1 AND resultat_elp.COD_ADM=1 AND gr.COD_SES = 1 AND gr.COD_ADM = 1 AND resultat_elp.COD_ANU=gr.COD_ANU) 
            ORDER BY resultat_elp.COD_ELP ASC,resultat_elp.COD_ANU ASC ,resultat_elp.COD_SES DESC"; 
                    
                   
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function resultat_vet($cn,$code,$typeResultat=null,$annee=null) {

        $query="SELECT DISTINCT(resultat_vet.COD_ETP), individu.COD_ETU, resultat_vet.COD_ANU, resultat_vet.COD_SES, resultat_vet.NOT_VET, resultat_vet.BAR_NOT_VET, resultat_vet.COD_TRE,  typ_resultat.LIC_TRE ,gr.ETA_AVC_VET ,resultat_vet.NOT_PNT_JUR_VET
                FROM individu JOIN resultat_vet ON (resultat_vet.COD_IND=individu.COD_IND) 
                    LEFT OUTER JOIN typ_resultat ON (resultat_vet.COD_TRE=typ_resultat.COD_TRE) LEFT JOIN grp_resultat_vet gr ON ( resultat_vet.COD_ETP = gr.COD_ETP )
                WHERE  (individu.COD_IND='".$code."' AND resultat_vet.COD_TRE LIKE '".$typeResultat."%' AND resultat_vet.COD_ANU LIKE '".$annee."%' 
                AND  resultat_vet.COD_SES =1 AND resultat_vet.COD_ADM=1 AND gr.COD_SES = 1 AND gr.COD_ADM = 1 AND resultat_vet.COD_ANU=gr.COD_ANU) 
                ORDER BY resultat_vet.COD_ANU ASC , resultat_vet.COD_ETP ASC,resultat_vet.COD_SES DESC";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function resultat_vet_global($cn,$code,$typeResultat=null,$annee=null) {

        $query="SELECT DISTINCT(resultat_vet.COD_ETP), individu.COD_ETU, resultat_vet.COD_ANU, resultat_vet.COD_SES, resultat_vet.NOT_VET, resultat_vet.BAR_NOT_VET, resultat_vet.COD_TRE,  typ_resultat.LIC_TRE ,gr.ETA_AVC_VET ,resultat_vet.NOT_PNT_JUR_VET
                FROM individu JOIN resultat_vet ON (resultat_vet.COD_IND=individu.COD_IND) 
                    LEFT OUTER JOIN typ_resultat ON (resultat_vet.COD_TRE=typ_resultat.COD_TRE) LEFT JOIN grp_resultat_vet gr ON ( resultat_vet.COD_ETP = gr.COD_ETP )
                WHERE  (individu.COD_IND='".$code."'  AND resultat_vet.COD_ANU LIKE '".$annee."%' 
                AND  resultat_vet.COD_SES =1 AND resultat_vet.COD_ADM=1 AND gr.COD_SES = 1 AND gr.COD_ADM = 1 AND resultat_vet.COD_ANU=gr.COD_ANU) 
                ORDER BY resultat_vet.COD_ANU ASC , resultat_vet.COD_ETP ASC,resultat_vet.COD_SES DESC";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }


     public function insAdmValidInd($code,$cn ,$etat,$cmp,$res) {

        $query="SELECT DISTINCT(r.COD_ANU),r.COD_ETP
                FROM ins_adm_etp ETP
                    LEFT OUTER JOIN resultat_vet r 
                    ON ETP.COD_ETP=r.COD_ETP
                WHERE  ETP.ETA_IAE='".$etat."'
                    AND  ETP.COD_CMP='".$cmp."'
                    AND  r.COD_IND='".$code."'
                    AND (r.COD_TRE like '".$res."%')
                    ORDER BY r.COD_ANU asc";
        $result= $cn->fetchAllAssociative($query);
        return  $result ; 

    }

    public function insAdmDiplomeInd($code,$cn ,$etat,$cmp,$res) {

        $query="SELECT r.COD_ANU,etp.COD_ETP,r.COD_DIP
                FROM  ins_adm_etp etp 
                    INNER JOIN resultat_vdi r 
                    ON (etp.COD_IND = r.COD_IND AND etp.COD_ANU= r.COD_ANU AND etp.COD_DIP=r.COD_DIP)   
                WHERE  etp.ETA_IAE='".$etat."'
                    AND  etp.COD_CMP='".$cmp."'
                    AND r.COD_IND= '".$code."'
                    AND (r.COD_TRE like '".$res."%')";
        $result= $cn->fetchAllAssociative($query);
        return  $result ; 

    }

    public function insAp2($code,$annee ,$etape,$cn) {

        $query="SELECT i.COD_IND,i.COD_ETU,rv.COD_TRE,rv.NOT_VET,rv.COD_ANU
                FROM individu i ,resultat_vet rv 
                where  i.COD_ETU='".$code."'
                    AND rv.cod_etp='".$etape."' 
                    AND rv.cod_anu='".$annee."'
                    AND (rv.COD_TRE='ADM' or rv.COD_TRE='ADMR')
                    AND i.COD_IND=rv.COD_IND";
        $result= $cn->fetchAllAssociative($query);
        return  $result ; 

    }

    
    public function chefFiliereEmail($diplome,$cn) {

        $query="SELECT u.email,p.nom,p.prenom,p.genre FROM utilisateurs u ,personnel p WHERE u.id = p.id_user_id and  u.roles like '%ROLE_CHEF_FIL%' and u.codes like '%FIL_".$diplome."%'";
        $result= $cn->fetchAssociative($query);
        return  $result ; 

    }


    public function getGroupeByInd($code,$annee,$cn) {

        $query="SELECT * FROM ind_affecte_gpe i LEFT JOIN groupe g on i.COD_GPE=g.COD_GPE where i.COD_ANU='".$annee."' and i.COD_IND='".$code."'";
        $result= $cn->fetchAssociative($query); 
        return  $result ; 

    }

    public function getExam($cn,$centre) {

        $query="SELECT * FROM periode_exa WHERE COD_CIN='".$centre."'";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function getExamPlanning($cn,$periode) {

        $query="SELECT ind.COD_ETU,ind.LIB_NOM_PAT_IND,ind.LIB_PR1_IND,e.LIB_EPR,prd.DAT_DEB_PES ,prd.COD_EPR, prd.DUR_EXA_EPR_PES , concat(prd.DHH_DEB_PES,concat(':',prd.DMM_DEB_PES)) as heure, prd.COD_SAL,concat(pa.LIB_NOM_PAT_PER,concat(' ',pa.LIB_PR1_PER))  as responsable
        FROM prd_epr_sal_anu prd 
          LEFT JOIN pes_ind ind on prd.COD_PES = ind.COD_PES 
          LEFT JOIN epreuve e on prd.COD_EPR=e.COD_EPR 
          LEFT JOIN sal_cin s on prd.COD_SAL=s.COD_SAL 
          LEFT JOIN res_epr pe on e.COD_EPR=pe.COD_EPR 
          LEFT JOIN personnel pa on pe.COD_PER=pa.COD_PER 
        WHERE prd.COD_PXA='".$periode."' AND ind.COD_ETU='22009894';";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function getExamSurveillantBySalle($cn,$periode,$salle,$epreuve) {

        $query="SELECT  prd.COD_SAL,concat(pa.LIB_NOM_PAT_PER,concat(' ',pa.LIB_PR1_PER))  as surveillant
        FROM prd_epr_sal_anu prd 
          INNER JOIN pes_per ind on prd.COD_PES = ind.COD_PES 
          LEFT JOIN sal_cin s on prd.COD_SAL=s.COD_SAL 
          LEFT JOIN personnel pa on ind.COD_PER=pa.COD_PER 
        WHERE prd.COD_PXA='".$periode."' AND prd.COD_SAL='".$salle."' AND prd.COD_EPR='".$epreuve."'";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }
    

    public function getEtatDelebiration($cn,$annee,$etape) {

        $query="SELECT * FROM grp_resultat_vet g WHERE g.COD_ANU= '".$annee."' and g.COD_ETP ='".$etape."' and g.COD_SES='1' and g.COD_ADM='1' ";
        $result= $cn->fetchAssociative($query); 
        return  $result ; 

    }

    public function getVersionReleve($cn,$etape) {

        $query="SELECT r.NUM_OCC_RVN FROM  releve_note r WHERE r.COD_OBJ_RVN='".$etape."'";
        $result= $cn->fetchAllAssociative($query); 
        return  $result ; 

    }

    public function etudiantinscritByInd($code,$cn,$etat,$cmp,$annee=null) {

        $query=" SELECT * FROM  individu i,ins_adm_etp etp WHERE i.COD_ETU='".$code."' and i.COD_IND = etp.COD_IND AND etp.COD_ANU LIKE '".$annee."' and etp.ETA_IAE='".$etat."' and etp.COD_CMP='".$cmp."'";
        $result= $cn->fetchAssociative($query);
          
        return  $result ; 

    }
//    /**
//     * @return Etudiants[] Returns an array of Etudiants objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Etudiants
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
