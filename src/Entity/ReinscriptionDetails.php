<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "reinscription_details")]
class ReinscriptionDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: "doctorant_id")]
    private $doctorantId;

    #[ORM\Column(type: "string", length: 255, name: "doctorant_full_name")]
    private $doctorantFullName;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "discipline")]
    private $discipline;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "specialite")]
    private $specialite;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "intitule_these")]
    private $intituleThese;

    #[ORM\Column(type: "text", nullable: true, name: "introduction")]
    private $introduction;

    #[ORM\Column(type: "text", nullable: true, name: "problematique")]
    private $problematique;

    #[ORM\Column(type: "text", nullable: true, name: "methodologie")]
    private $methodologie;

    #[ORM\Column(type: "text", nullable: true, name: "resultats")]
    private $resultats;

    #[ORM\Column(type: "text", nullable: true, name: "conclusion")]
    private $conclusion;

    #[ORM\Column(type: "text", nullable: true, name: "travaux_en_attente")]
    private $travauxEnAttente;

    #[ORM\Column(type: "boolean", nullable: true, name: "bourse_merite")]
    private $bourseMerite;

    #[ORM\Column(type: "date", nullable: true, name: "bourse_merite_depuis")]
    private $bourseMeriteDepuis;

    #[ORM\Column(type: "boolean", nullable: true, name: "bourse_troisieme_cycle")]
    private $bourseTroisiemeCycle;

    #[ORM\Column(type: "date", nullable: true, name: "bourse_troisieme_cycle_depuis")]
    private $bourseTroisiemeCycleDepuis;

    #[ORM\Column(type: "boolean", nullable: true, name: "bourse_cotutelle")]
    private $bourseCotutelle;

    #[ORM\Column(type: "date", nullable: true, name: "bourse_cotutelle_date_debut")]
    private $bourseCotutelleDateDebut;

    #[ORM\Column(type: "date", nullable: true, name: "bourse_cotutelle_date_fin")]
    private $bourseCotutelleDateFin;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "bourse_echange")]
    private $bourseEchange;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "bourse_projet_recherche")]
    private $bourseProjetRecherche;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "salarie_fonction")]
    private $salarieFonction;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "salarie_organisme")]
    private $salarieOrganisme;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "fonctionnaire_fonction")]
    private $fonctionnaireFonction;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "fonctionnaire_organisme")]
    private $fonctionnaireOrganisme;

    #[ORM\Column(type: "boolean", nullable: true, name: "cotutelle")]
    private $cotutelle;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "cotutelle_universite")]
    private $cotutelleUniversite;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "cotutelle_nom_prenom")]
    private $cotutelleNomPrenom;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "cotutelle_telephone")]
    private $cotutelleTelephone;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "cotutelle_email")]
    private $cotutelleEmail;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "pdf_path")]
    private $pdfPath;

    #[ORM\Column(type: "string", length: 50, name: "statut")]
    private $statut = 'En cours';

    #[ORM\OneToMany(targetEntity: FormationComplementaire::class, mappedBy: "reinscriptionDetails", cascade: ["persist", "remove"])]
    private $formationsComplementaires;

    public function __construct()
    {
        $this->formationsComplementaires = new ArrayCollection();
        $this->statut = 'En cours';
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getDoctorantId(): ?int { return $this->doctorantId; }
    public function setDoctorantId(int $doctorantId): self { $this->doctorantId = $doctorantId; return $this; }

    public function getDoctorantFullName(): ?string { return $this->doctorantFullName; }
    public function setDoctorantFullName(string $doctorantFullName): self { $this->doctorantFullName = $doctorantFullName; return $this; }

    public function getDiscipline(): ?string { return $this->discipline; }
    public function setDiscipline(?string $discipline): self { $this->discipline = $discipline; return $this; }

    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(?string $specialite): self { $this->specialite = $specialite; return $this; }

    public function getIntituleThese(): ?string { return $this->intituleThese; }
    public function setIntituleThese(?string $intituleThese): self { $this->intituleThese = $intituleThese; return $this; }

    public function getIntroduction(): ?string { return $this->introduction; }
    public function setIntroduction(?string $introduction): self { $this->introduction = $introduction; return $this; }

    public function getProblematique(): ?string { return $this->problematique; }
    public function setProblematique(?string $problematique): self { $this->problematique = $problematique; return $this; }

    public function getMethodologie(): ?string { return $this->methodologie; }
    public function setMethodologie(?string $methodologie): self { $this->methodologie = $methodologie; return $this; }

    public function getResultats(): ?string { return $this->resultats; }
    public function setResultats(?string $resultats): self { $this->resultats = $resultats; return $this; }

    public function getConclusion(): ?string { return $this->conclusion; }
    public function setConclusion(?string $conclusion): self { $this->conclusion = $conclusion; return $this; }

    public function getTravauxEnAttente(): ?string { return $this->travauxEnAttente; }
    public function setTravauxEnAttente(?string $travauxEnAttente): self { $this->travauxEnAttente = $travauxEnAttente; return $this; }

    public function getBourseMerite(): ?bool { return $this->bourseMerite; }
    public function setBourseMerite(?bool $bourseMerite): self { $this->bourseMerite = $bourseMerite; return $this; }

    public function getBourseMeriteDepuis(): ?\DateTimeInterface { return $this->bourseMeriteDepuis; }
    public function setBourseMeriteDepuis(?\DateTimeInterface $bourseMeriteDepuis): self { $this->bourseMeriteDepuis = $bourseMeriteDepuis; return $this; }

    public function getBourseTroisiemeCycle(): ?bool { return $this->bourseTroisiemeCycle; }
    public function setBourseTroisiemeCycle(?bool $bourseTroisiemeCycle): self { $this->bourseTroisiemeCycle = $bourseTroisiemeCycle; return $this; }

    public function getBourseTroisiemeCycleDepuis(): ?\DateTimeInterface { return $this->bourseTroisiemeCycleDepuis; }
    public function setBourseTroisiemeCycleDepuis(?\DateTimeInterface $bourseTroisiemeCycleDepuis): self { $this->bourseTroisiemeCycleDepuis = $bourseTroisiemeCycleDepuis; return $this; }

    public function getBourseCotutelle(): ?bool { return $this->bourseCotutelle; }
    public function setBourseCotutelle(?bool $bourseCotutelle): self { $this->bourseCotutelle = $bourseCotutelle; return $this; }

    public function getBourseCotutelleDateDebut(): ?\DateTimeInterface { return $this->bourseCotutelleDateDebut; }
    public function setBourseCotutelleDateDebut(?\DateTimeInterface $bourseCotutelleDateDebut): self { $this->bourseCotutelleDateDebut = $bourseCotutelleDateDebut; return $this; }

    public function getBourseCotutelleDateFin(): ?\DateTimeInterface { return $this->bourseCotutelleDateFin; }
    public function setBourseCotutelleDateFin(?\DateTimeInterface $bourseCotutelleDateFin): self { $this->bourseCotutelleDateFin = $bourseCotutelleDateFin; return $this; }

    public function getBourseEchange(): ?string { return $this->bourseEchange; }
    public function setBourseEchange(?string $bourseEchange): self { $this->bourseEchange = $bourseEchange; return $this; }

    public function getBourseProjetRecherche(): ?string { return $this->bourseProjetRecherche; }
    public function setBourseProjetRecherche(?string $bourseProjetRecherche): self { $this->bourseProjetRecherche = $bourseProjetRecherche; return $this; }

    public function getSalarieFonction(): ?string { return $this->salarieFonction; }
    public function setSalarieFonction(?string $salarieFonction): self { $this->salarieFonction = $salarieFonction; return $this; }

    public function getSalarieOrganisme(): ?string { return $this->salarieOrganisme; }
    public function setSalarieOrganisme(?string $salarieOrganisme): self { $this->salarieOrganisme = $salarieOrganisme; return $this; }

    public function getFonctionnaireFonction(): ?string { return $this->fonctionnaireFonction; }
    public function setFonctionnaireFonction(?string $fonctionnaireFonction): self { $this->fonctionnaireFonction = $fonctionnaireFonction; return $this; }

    public function getFonctionnaireOrganisme(): ?string { return $this->fonctionnaireOrganisme; }
    public function setFonctionnaireOrganisme(?string $fonctionnaireOrganisme): self { $this->fonctionnaireOrganisme = $fonctionnaireOrganisme; return $this; }

    public function getCotutelle(): ?bool { return $this->cotutelle; }
    public function setCotutelle(?bool $cotutelle): self { $this->cotutelle = $cotutelle; return $this; }

    public function getCotutelleUniversite(): ?string { return $this->cotutelleUniversite; }
    public function setCotutelleUniversite(?string $cotutelleUniversite): self { $this->cotutelleUniversite = $cotutelleUniversite; return $this; }

    public function getCotutelleNomPrenom(): ?string { return $this->cotutelleNomPrenom; }
    public function setCotutelleNomPrenom(?string $cotutelleNomPrenom): self { $this->cotutelleNomPrenom = $cotutelleNomPrenom; return $this; }

    public function getCotutelleTelephone(): ?string { return $this->cotutelleTelephone; }
    public function setCotutelleTelephone(?string $cotutelleTelephone): self { $this->cotutelleTelephone = $cotutelleTelephone; return $this; }

    public function getCotutelleEmail(): ?string { return $this->cotutelleEmail; }
    public function setCotutelleEmail(?string $cotutelleEmail): self { $this->cotutelleEmail = $cotutelleEmail; return $this; }

    public function getPdfPath(): ?string { return $this->pdfPath; }
    public function setPdfPath(?string $pdfPath): self { $this->pdfPath = $pdfPath; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }

    /**
     * @return Collection|FormationComplementaire[]
     */
    public function getFormationsComplementaires(): Collection { return $this->formationsComplementaires; }

    public function addFormationComplementaire(FormationComplementaire $formationComplementaire): self
    {
        if (!$this->formationsComplementaires->contains($formationComplementaire)) {
            $this->formationsComplementaires[] = $formationComplementaire;
            $formationComplementaire->setReinscriptionDetails($this);
        }
        return $this;
    }

    public function removeFormationComplementaire(FormationComplementaire $formationComplementaire): self
    {
        if ($this->formationsComplementaires->removeElement($formationComplementaire)) {
            if ($formationComplementaire->getReinscriptionDetails() === $this) {
                $formationComplementaire->setReinscriptionDetails(null);
            }
        }
        return $this;
    }
}