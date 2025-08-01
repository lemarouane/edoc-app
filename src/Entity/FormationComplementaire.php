<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "formation_complementaire")]
class FormationComplementaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: ReinscriptionDetails::class, inversedBy: "formationsComplementaires")]
    #[ORM\JoinColumn(name: "reinscription_details_id", nullable: false)]
    private $reinscriptionDetails;

    #[ORM\Column(type: "date", nullable: true, name: "date")]
    private $date;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "duree")]
    private $duree;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "intitule")]
    private $intitule;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "organisateur")]
    private $organisateur;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "equivalence_heures")]
    private $equivalenceHeures;

    public function getId(): ?int { return $this->id; }

    public function getReinscriptionDetails(): ?ReinscriptionDetails { return $this->reinscriptionDetails; }
    public function setReinscriptionDetails(?ReinscriptionDetails $reinscriptionDetails): self { $this->reinscriptionDetails = $reinscriptionDetails; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(?\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getDuree(): ?string { return $this->duree; }
    public function setDuree(?string $duree): self { $this->duree = $duree; return $this; }

    public function getIntitule(): ?string { return $this->intitule; }
    public function setIntitule(?string $intitule): self { $this->intitule = $intitule; return $this; }

    public function getOrganisateur(): ?string { return $this->organisateur; }
    public function setOrganisateur(?string $organisateur): self { $this->organisateur = $organisateur; return $this; }

    public function getEquivalenceHeures(): ?string { return $this->equivalenceHeures; }
    public function setEquivalenceHeures(?string $equivalenceHeures): self { $this->equivalenceHeures = $equivalenceHeures; return $this; }
}