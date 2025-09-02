<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\DBAL\Connection;
use App\Entity\EtuReleveAttestation;
use App\Entity\EtuAttestation;
use App\Entity\EtuDiplomeCarte;
use App\Entity\Etudiants;
use App\Entity\image;
use App\Entity\Laureats;
use App\Form\LaureatsType;
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
use App\Entity\Cvtheque;
use App\Repository\CvthequeRepository;

class DocumentController extends AbstractController
{
    #[Route(path: '/document', name: 'app_document')]
    public function document(Security $security, Request $request, Connection $conn)
    {
        $usr = $security->getUser();
        $em = $this->getDoctrine()->getManager('default');
        $em1 = $this->getDoctrine()->getManager('customer');
        $param = new ConfigExtension($em1);

        $etudiant = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(), $conn);

        $ins_Adm_E = $em->getRepository(Etudiants::class)->insAdmValidInd($etudiant["COD_IND"], $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $param->app_config('COD_ADM'));

        $ins_Adm_AT = $em->getRepository(Etudiants::class)->insAdmValidInd($etudiant["COD_IND"], $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $param->app_config('COD_ADM'));

        $ins_dip = $em->getRepository(Etudiants::class)->insAdmDiplomeInd($etudiant["COD_IND"], $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $param->app_config('COD_ADM'));

        $releveValide = $em->getRepository(EtuReleveAttestation::class)->docBycodeNonRefu($usr->getId(), 'Relevé');
        $tab = array();
        if (!empty($releveValide)) {
            foreach ($releveValide as $releve) {
                if (($key = array_search($releve->getCodeEtape(), array_column($ins_Adm_E, 'COD_ETP'))) !== false) {
                    array_push($tab, $key);
                }
            }
        }
        for ($i = 0; $i < count($tab); $i++) {
            unset($ins_Adm_E[$tab[$i]]);
        }

        $attestationValide = $em->getRepository(EtuReleveAttestation::class)->docBycodeNonRefu($usr->getId(), 'Attestation');

        $tab1 = array();
        if (!empty($attestationValide)) {
            foreach ($attestationValide as $attestation) {
                if (($key1 = array_search($attestation->getCodeEtape(), array_column($ins_Adm_AT, 'COD_ETP'))) !== false) {
                    array_push($tab1, $key1);
                }
            }
        }
        for ($j = 0; $j < count($tab1); $j++) {
            unset($ins_Adm_AT[$tab1[$j]]);
        }

        $releves = $em->getRepository(EtuReleveAttestation::class)->findby(array('codeEtudiant' => $usr));
        $diplomes = $em->getRepository(EtuDiplomeCarte::class)->findby(array('codeEtudiant' => $usr, 'type' => 'Diplome'));
        $cartes = $em->getRepository(EtuDiplomeCarte::class)->findby(array('codeEtudiant' => $usr, 'type' => 'Carte'));
        $attestations = $em->getRepository(EtuAttestation::class)->findby(array('codeEtudiant' => $usr));
        $laureat = new Laureats();
        $form = $this->createForm(LaureatsType::class, $laureat);
        $form->handleRequest($request);

        // Check CVthèque completion
        $hasCv = $em->getRepository(Cvtheque::class)->cvExist($usr->getId()) === "True";

        // Check for validated PFE rapport with encadrant_id
        $hasValidRapport = $conn->fetchOne(
            'SELECT 1 FROM pgi_doc_db.rapport_pfe WHERE etudiant_code = :cod_etu AND status = :status AND encadrant_id IS NOT NULL',
            ['cod_etu' => $etudiant["COD_ETU"], 'status' => 'Validé']
        ) !== false;

        // Combine conditions
        $hasValidRapportAndCv = $hasCv && $hasValidRapport;

        $ins_Adm_E = array_change_key_case($ins_Adm_E, CASE_UPPER);
        $ins_Adm_AT = array_change_key_case($ins_Adm_AT, CASE_UPPER);

        if ($usr->getType() == 'FI') {
            return $this->render('documents/demande.html.twig', [
                'form' => $form->createView(),
                'ins_dip' => $ins_dip,
                'ins_etape' => $ins_Adm_E,
                'etudiant' => $etudiant,
                'ins_etape_AT' => $ins_Adm_AT,
                'releves' => $releves,
                'diplomes' => $diplomes,
                'cartes' => $cartes,
                'attestations' => $attestations,
                'hasValidRapportAndCv' => $hasValidRapportAndCv
            ]);
        } else {
            return $this->render('documents/demande.html.twig', [
                'form' => $form->createView(),
                'ins_dip' => $ins_dip,
                'ins_etape' => $ins_Adm_E,
                'etudiant' => $etudiant,
                'ins_etape_AT' => $ins_Adm_AT,
                'releves' => $releves,
                'diplomes' => $diplomes,
                'cartes' => $cartes,
                'attestations' => $attestations,
                'hasValidRapportAndCv' => $hasValidRapportAndCv
            ]);
        }
    }

    #[Route(path: '/demandeDoc', name: 'app_demandeDoc')]
    public function demandeDocAction(Security $security, Request $request, Connection $conn, TranslatorInterface $translator)
    {
        $usr = $security->getUser();
        $em = $this->getDoctrine()->getManager('default');
        $em1 = $this->getDoctrine()->getManager('customer');
        $param = new ConfigExtension($em1);

        $laureat = new Laureats();
        $form = $this->createForm(LaureatsType::class, $laureat);
        $form->handleRequest($request);

        $anneeUniversitaire = $em->getRepository(Etudiants::class)->getAnneeUnivEncours($conn);
        $etudiant = $em->getRepository(Etudiants::class)->etudiantByInd($usr->getCode(), $conn);

        $releves = $request->get('releve');
        $attestationR = $request->get('attestationR');
        $document = $request->get('document');

        $ins_Adm_E = $em->getRepository(Etudiants::class)->insAdmValidInd($etudiant["COD_IND"], $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $param->app_config('COD_ADM'));
        $ins_dip = $em->getRepository(Etudiants::class)->insAdmDiplomeInd($etudiant["COD_IND"], $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $param->app_config('COD_ADM'));

        // traitement des relevés de notes
        if (!empty($releves)) {
            foreach ($releves as $key) {
                $releveAttestation = new EtuReleveAttestation();
                $releveAttestation->setDateDemande(new \DateTime('now'));
                $releveAttestation->setCodeEtudiant($usr);
                $releveAttestation->setAnneeUniv($anneeUniversitaire['COD_ANU']);
                $releveAttestation->setCodeEtudiant($usr);
                $releve = explode("_", $key);
                $codeEtape = $releve[0];
                $anneEtape = $releve[1];
                $releveArray = array("COD_ANU" => $anneEtape, "COD_ETP" => $codeEtape);

                if (in_array($releveArray, $ins_Adm_E)) {
                    $releveAttestation->setType('Relevé');
                    $releveAttestation->setAnneeEtape($anneEtape);
                    $releveAttestation->setCodeEtape($codeEtape);
                    $releveAttestation->setTypeF($em->getRepository(Etudiants::class)->findOneBy(array('id' => $usr->getId()))->getType());
                    $version = $em->getRepository(Etudiants::class)->getVersionReleve($conn, $codeEtape);
                    $ver1 = array();
                    foreach ($version as $key) {
                        array_push($ver1, $key["NUM_OCC_RVN"]);
                    }
                    $releveAttestation->setVersion($ver1);
                    $releveAttestation->setVersion1($ver1[0]);
                    $releveAttestation->setDecision('-1');

                    $releveValide = $em->getRepository(EtuReleveAttestation::class)->rechercheBy($usr->getId(), $codeEtape, $anneEtape, 'Relevé');

                    if (empty($releveValide)) {
                        $em->persist($releveAttestation);
                        $this->addFlash('success', $translator->trans('msg_doc_1') . " " . $codeEtape . ")");
                    } else {
                        $this->addFlash('danger', $translator->trans('msg_doc_2') . " " . $releveValide[0]->getCodeEtape() . " " . $translator->trans('le') . " " . $releveValide[0]->getDateDemande()->format('d-m-Y H:i:s') . ".");
                    }
                } else {
                    $this->addFlash('danger', "msg_doc_3");
                    return new RedirectResponse($this->generateUrl('app_document'));
                }
            }
        }
        // traitement des Attestations de Réussite
        if (!empty($attestationR)) {
            foreach ($attestationR as $key1) {
                $releveAttestation1 = new EtuReleveAttestation();
                $releveAttestation1->setDateDemande(new \DateTime('now'));
                $releveAttestation1->setCodeEtudiant($usr);
                $releveAttestation1->setAnneeUniv($anneeUniversitaire['COD_ANU']);
                $releveAttestation1->setCodeEtudiant($usr);
                $attestation = explode("_", $key1);
                $codeEtape = $attestation[0];
                $anneEtape = $attestation[1];
                $attestationArray = array("COD_ANU" => $anneEtape, "COD_ETP" => $codeEtape);

                if (in_array($attestationArray, $ins_Adm_E)) {
                    $releveAttestation1->setType('Attestation');
                    $releveAttestation1->setAnneeEtape($anneEtape);
                    $releveAttestation1->setCodeEtape($codeEtape);
                    $releveAttestation1->setTypeF($em->getRepository(Etudiants::class)->findOneBy(array('id' => $usr->getId()))->getType());
                    $releveAttestation1->setDecision('-1');

                    $attestationValide = $em->getRepository(EtuReleveAttestation::class)->rechercheBy($usr->getId(), $codeEtape, $anneEtape, 'Attestation');

                    if (empty($attestationValide)) {
                        $em->persist($releveAttestation1);
                        $this->addFlash('success', $translator->trans('msg_doc_4') . " " . $codeEtape . ")");
                    } else {
                        $this->addFlash('danger', $translator->trans('msg_doc_5') . " " . $attestationValide[0]->getCodeEtape() . " " . $translator->trans('le') . " " . $attestationValide[0]->getDateDemande()->format('d-m-Y H:i:s'));
                    }
                } else {
                    $this->addFlash('danger', "msg_doc_6");
                    return new RedirectResponse($this->generateUrl('app_document'));
                }
            }
        }

        if (isset($document['carte'])) {
            $diplomeCarte = new EtuDiplomeCarte();
            $diplomeCarte->setDateDemande(new \DateTime('now'));
            $diplomeCarte->setCodeEtudiant($usr);
            $diplomeCarte->setAnneeUniv($anneeUniversitaire['COD_ANU']);
            $diplomeCarte->setCodeEtudiant($usr);

            $diplomeCarte->setTypeF($em->getRepository(Etudiants::class)->findOneBy(array('id' => $usr->getId()))->getType());
            $carte = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision1($usr->getId(), 'Originale', 'Carte');
            $carteD = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision1($usr->getId(), 'Duplicata', 'Carte');
            if ($carte) {
                if ($document['carte'] == 'Originale') {
                    $this->addFlash('danger', "msg_doc_7");
                } elseif ($document['carte'] == 'Duplicata') {
                    if ($carteD) {
                        $this->addFlash('danger', "msg_doc_8");
                    } else {
                        $carteE = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision($usr->getId(), 'Originale', 'Carte');
                        if ($carteE) {
                            $diplomeCarte->setType('Carte');
                            $diplomeCarte->setValueType('Duplicata');
                            $diplomeCarte->setDecision('-1');
                            $em->persist($diplomeCarte);
                            $this->addFlash('success', "msg_doc_9");
                        } else {
                            $this->addFlash('danger', "msg_doc_10");
                        }
                    }
                }
            } else {
                if ($document['carte'] == 'Originale' && $usr->getImage()->getPath() != 'anonymous.png') {
                    $diplomeCarte->setType('Carte');
                    $diplomeCarte->setValueType('Originale');
                    $diplomeCarte->setDecision('-1');
                    $em->persist($diplomeCarte);
                    $this->addFlash('success', "msg_doc_11");
                } elseif ($document['carte'] == 'Duplicata') {
                    $this->addFlash('danger', "msg_doc_12");
                } else {
                    $this->addFlash('danger', "msg_doc_13");
                }
            }
        }
        if (isset($document['diplome'])) {
            // Check CVthèque and validated PFE rapport before allowing diploma request
            $hasCv = $em->getRepository(Cvtheque::class)->cvExist($usr->getId()) === "True";
            $hasValidRapport = $conn->fetchOne(
                'SELECT 1 FROM pgi_doc_db.rapport_pfe WHERE etudiant_code = :cod_etu AND status = :status AND encadrant_id IS NOT NULL',
                ['cod_etu' => $etudiant["COD_ETU"], 'status' => 'Validé']
            ) !== false;

            if (!$hasCv || !$hasValidRapport) {
                $this->addFlash('danger', "msg_doc_cvtheque_or_rapport");
            } else {
                if (!empty($ins_dip)) {
                    $diplomeCarte1 = new EtuDiplomeCarte();
                    $diplomeCarte1->setDateDemande(new \DateTime('now'));
                    $diplomeCarte1->setAnneeUniv($ins_dip[0]['COD_ANU']);
                    $diplomeCarte1->setCodeEtudiant($usr);
                    $diplomeCarte1->setFiliere($ins_dip[0]['COD_DIP']);
                    $diplomeCarte1->setTypeF($em->getRepository(Etudiants::class)->findOneBy(array('id' => $usr->getId()))->getType());
                    $diplome = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision1($usr->getId(), 'Originale', 'Diplome');
                    $diplomeD = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision1($usr->getId(), 'Duplicata', 'Diplome');
                    if ($diplome) {
                        if ($document['diplome'] == 'Originale') {
                            $this->addFlash('danger', "msg_doc_14");
                        } elseif ($document['diplome'] == 'Duplicata') {
                            if ($diplomeD) {
                                $this->addFlash('danger', "msg_doc_15");
                            } else {
                                $diplomeE = $em->getRepository(EtuDiplomeCarte::class)->rechercheByDecision($usr->getId(), 'Originale', 'Diplome');
                                if ($diplomeE) {
                                    $diplomeCarte1->setType('Diplome');
                                    $diplomeCarte1->setValueType('Duplicata');
                                    $diplomeCarte1->setDecision('-1');
                                    $em->persist($diplomeCarte1);
                                    $this->addFlash('success', "msg_doc_16");
                                } else {
                                    $this->addFlash('danger', "msg_doc_17");
                                }
                            }
                        }
                    } else {
                        if ($document['diplome'] == 'Originale') {
                            $diplomeCarte1->setType('Diplome');
                            $diplomeCarte1->setValueType('Originale');
                            $diplomeCarte1->setDecision('-1');

                            $laureat->setidUser($em->getRepository(Etudiants::class)->find($usr->getId()));
                            $em->persist($diplomeCarte1);
                            $this->addFlash('success', "msg_doc_22");
                        } elseif ($document['diplome'] == 'Duplicata') {
                            $this->addFlash('danger', "msg_doc_23");
                        }
                    }
                } else {
                    $this->addFlash('danger', "msg_doc_24");
                }
            }
        }

        if (isset($document['attestation'])) {
            $inscriptionEtudiant = $em->getRepository(Etudiants::class)->etudiantinscritByInd($usr->getCode(), $conn, $param->app_config('ETA_IAE'), $param->app_config('COD_CMP'), $anneeUniversitaire['COD_ANU']);
            if ($inscriptionEtudiant) {
                $attestation = new EtuAttestation();
                $attestation->setTypeF($em->getRepository(Etudiants::class)->findOneBy(array('id' => $usr->getId()))->getType());
                $attestation->setDateDemande(new \DateTime('now'));
                $attestation->setAnneeUniv($anneeUniversitaire['COD_ANU']);
                $attestation->setCodeEtudiant($usr);
                $attestationDem = $em->getRepository(EtuAttestation::class)->findBy(array('codeEtudiant' => $usr->getId(), 'anneeUniv' => $anneeUniversitaire['COD_ANU']), array('dateDemande' => 'DESC'), $limit = 1);
                if ($attestationDem) {
                    if ($attestationDem[0]->getDecision() == '-1') {
                        $this->addFlash('danger', "msg_doc_25");
                    } else {
                        if ($attestationDem[0]->getDecision() == '0') {
                            $attestationAcc = $em->getRepository(EtuAttestation::class)->findBy(array('codeEtudiant' => $usr->getId(), 'anneeUniv' => $anneeUniversitaire['COD_ANU'], 'decision' => '1'), array('dateDemande' => 'DESC'), $limit = 1);
                            if ($attestationAcc) {
                                $date = new \DateTime();
                                $result = $date->format('d-m-Y H:i:s');

                                $diff = strtotime($result) - strtotime($attestationAcc[0]->getDateValidation()->format('d-m-Y H:i:s'));
                                $nbJours = $diff / 86400;
                                if ($nbJours >= 90) {
                                    $this->addFlash('success', "msg_doc_26");
                                    $attestation->setDecision('-1');
                                    $em->persist($attestation);
                                } else {
                                    $y = date('d-m-Y H:i:s', strtotime('+3 month', strtotime($attestationAcc[0]->getDateValidation()->format('d-m-Y H:i:s'))));
                                    $this->addFlash('danger', $translator->trans('msg_doc_27') . " " . $attestationAcc[0]->getDateValidation()->format('d-m-Y H:i:s') . " , " . $translator->trans('msg_doc_27_bis') . " " . $y . ".");
                                }
                            } else {
                                $this->addFlash('success', "msg_doc_28");
                                $attestation->setDecision('-1');
                                $em->persist($attestation);
                            }
                        } else {
                            $date1 = new \DateTime();
                            $result1 = $date1->format('d-m-Y H:i:s');

                            $diff1 = strtotime($result1) - strtotime($attestationDem[0]->getDateValidation()->format('d-m-Y H:i:s'));
                            $nbJours1 = $diff1 / 86400;
                            if ($nbJours1 >= 90) {
                                $this->addFlash('success', "msg_doc_29");
                                $attestation->setDecision('-1');
                                $em->persist($attestation);
                            } else {
                                $y1 = date('d-m-Y H:i:s', strtotime('+3 month', strtotime($attestationDem[0]->getDateValidation()->format('d-m-Y H:i:s'))));
                                $this->addFlash('danger', $translator->trans('msg_doc_30') . " " . $attestationDem[0]->getDateValidation()->format('d-m-Y H:i:s') . " , " . $translator->trans('msg_doc_30_bis') . " " . $y1 . ".");
                            }
                        }
                    }
                } else {
                    $this->addFlash('success', "msg_doc_31");
                    $attestation->setDecision('-1');
                    $em->persist($attestation);
                }
            } else {
                $this->addFlash('danger', "msg_doc_24");
            }
        }
        $em->flush();

        return new RedirectResponse($this->generateUrl('app_document'));
    }

    /**
     * @Route("/telechargerDoc/{type}", name="telechargerDoc")
     */
    public function download(Security $security, $type): Response
    {
        $usr = $security->getUser();
        $finder = new Finder();
        if ($type == 'B') {
            $finder->files()->in($this->getParameter('upload_doc'))->name($usr->getCode() . '.pdf');
            if ($finder->hasResults()) {
                $file = new File($this->getParameter('upload_doc') . $usr->getCode() . '.pdf');
                return $this->file($file);
            } else {
                $this->addFlash('danger', "msg_doc_32");
                return new RedirectResponse($this->generateUrl('app_dashboard'));
            }
        } elseif ($type == 'D') {
            $finder1 = $finder->files()->in($this->getParameter('upload_doc'))->name($usr->getCode() . '_d.pdf');
            if ($finder1->hasResults()) {
                $file = new File($this->getParameter('upload_doc') . $usr->getCode() . '_d.pdf');
                return $this->file($file);
            } else {
                $this->addFlash('danger', "msg_doc_33");
                return new RedirectResponse($this->generateUrl('app_dashboard'));
            }
        } elseif ($type == 'L') {
            $finder3 = $finder->files()->in($this->getParameter('upload_doc'))->name($usr->getCode() . '_l.pdf');
            if ($finder3->hasResults()) {
                $file = new File($this->getParameter('upload_doc') . $usr->getCode() . '_l.pdf');
                return $this->file($file);
            } else {
                $this->addFlash('danger', "msg_doc_34");
                return new RedirectResponse($this->generateUrl('app_dashboard'));
            }
        } else {
            $this->addFlash('danger', "msg_doc_35");
            return new RedirectResponse($this->generateUrl('app_dashboard'));
        }
    }

    /**
     * @Route("/edit_image", name="edit_image")
     */
    public function edit_image(Security $security, Request $request)
    {
        $usr = $security->getUser();
        $em = $this->getDoctrine()->getManager('default');

        $lien = $request->files->get('fileemploi');

        if ($lien) {
            $originalFilename = pathinfo($lien->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = sha1(uniqid(mt_rand(), true)) . '.' . $lien->guessExtension();

            try {
                if ($usr->getImage()->getPath() != 'anonymous.png') {
                    unlink($usr->getImage()->getAbsolutePath());
                }
                $lien->move($usr->getImage()->getUploadRootDir(), $newFilename);
                $usr->getImage()->setPath($newFilename);

                $em->persist($usr);
                $em->flush();
                return new JsonResponse("1");
            } catch (FileException $e) {
                return new JsonResponse("0");
            }
        } else {
            return new JsonResponse("0");
        }
    }
}