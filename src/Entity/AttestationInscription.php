<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "attestation_inscription")]
class AttestationInscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(name: "doctorant_id", type: "integer")]
    private int $doctorantId;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(name: "date_demande", type: "datetime")]
    private \DateTimeInterface $dateDemande;

    #[ORM\Column(type: "string", length: 20, options: ["default" => "En cours"])]
    private string $etat = "En cours";

    #[ORM\Column(name: "annee_univ", type: "string", length: 10, nullable: true)]
    private ?string $anneeUniv = null;



    #[ORM\Column(name: "id_validateur", type: "integer", nullable: true)]
    private ?int $idValidateur = null;

    #[ORM\Column(name: "validateur", type: "string", length: 255, nullable: true)]
    private ?string $validateur = null;




    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getDoctorantId(): int
    {
        return $this->doctorantId;
    }

    public function setDoctorantId(int $doctorantId): self
    {
        $this->doctorantId = $doctorantId;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateDemande(): \DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }

    public function getEtat(): string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getAnneeUniv(): ?string
    {
        return $this->anneeUniv;
    }

    public function setAnneeUniv(?string $anneeUniv): self
    {
        $this->anneeUniv = $anneeUniv;
        return $this;
    }






        // Getters and Setters for the new fields
    public function getIdValidateur(): ?int
    {
        return $this->idValidateur;
    }

    public function setIdValidateur(?int $idValidateur): self
    {
        $this->idValidateur = $idValidateur;
        return $this;
    }

    public function getValidateur(): ?string
    {
        return $this->validateur;
    }

    public function setValidateur(?string $validateur): self
    {
        $this->validateur = $validateur;
        return $this;
    }
}

