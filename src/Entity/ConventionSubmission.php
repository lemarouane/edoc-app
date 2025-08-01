<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "convention_submission")]
class ConventionSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(name: "etudiant_id", type: "integer")]
    private int $etudiantId;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(name: "convention_id", type: "integer")]
    private int $conventionId;

    #[ORM\Column(name: "zip_file", type: "string", length: 255)]
    private string $zipFile;

    #[ORM\Column(name: "date_submission", type: "datetime")]
    private \DateTimeInterface $dateSubmission;

    #[ORM\Column(type: "string", length: 20, options: ["default" => "En cours"])]
    private string $etat = "En cours";

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $mcal = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $note2 = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remarque = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $anneeDepart = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $anneeSoutenance = null;

    // Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getEtudiantId(): int
    {
        return $this->etudiantId;
    }

    public function setEtudiantId(int $etudiantId): self
    {
        $this->etudiantId = $etudiantId;
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

    public function getConventionId(): int
    {
        return $this->conventionId;
    }

    public function setConventionId(int $conventionId): self
    {
        $this->conventionId = $conventionId;
        return $this;
    }

    public function getZipFile(): string
    {
        return $this->zipFile;
    }

    public function setZipFile(string $zipFile): self
    {
        $this->zipFile = $zipFile;
        return $this;
    }

    public function getDateSubmission(): \DateTimeInterface
    {
        return $this->dateSubmission;
    }

    public function setDateSubmission(\DateTimeInterface $dateSubmission): self
    {
        $this->dateSubmission = $dateSubmission;
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

    public function getMcal(): ?float
    {
        return $this->mcal;
    }

    public function setMcal(?float $mcal): self
    {
        $this->mcal = $mcal;
        return $this;
    }

    public function getNote2(): ?float
    {
        return $this->note2;
    }

    public function setNote2(?float $note2): self
    {
        $this->note2 = $note2;
        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): self
    {
        $this->remarque = $remarque;
        return $this;
    }

    public function getAnneeDepart(): ?int
    {
        return $this->anneeDepart;
    }

    public function setAnneeDepart(?int $anneeDepart): self
    {
        $this->anneeDepart = $anneeDepart;
        return $this;
    }

    public function getAnneeSoutenance(): ?int
    {
        return $this->anneeSoutenance;
    }

    public function setAnneeSoutenance(?int $anneeSoutenance): self
    {
        $this->anneeSoutenance = $anneeSoutenance;
        return $this;
    }
}