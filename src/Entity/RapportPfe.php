<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rapport_pfe', schema: 'pgi_doc_db')]
class RapportPfe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, name: 'file_path')]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::INTEGER, name: 'stage_id')]
    private ?int $stageId = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $filiere = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $sujet = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'date_debut')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'date_fin')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::INTEGER, name: 'etudiant_id')]
    private ?int $etudiantId = null;

    #[ORM\Column(type: Types::STRING, length: 255, name: 'etudiant_nom')]
    private ?string $etudiantNom = null;

    #[ORM\Column(type: Types::STRING, length: 255, name: 'etudiant_prenom')]
    private ?string $etudiantPrenom = null;

    #[ORM\Column(type: Types::STRING, length: 20, name: 'etudiant_code')]
    private ?string $etudiantCode = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, name: 'encadrant_id')]
    private ?int $encadrantId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, name: 'encadrant_nom')]
    private ?string $encadrantNom = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, name: 'encadrant_prenom')]
    private ?string $encadrantPrenom = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, name: 'encadrant_role')]
    private ?string $encadrantRole = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'updated_at')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStageId(): ?int
    {
        return $this->stageId;
    }

    public function setStageId(int $stageId): self
    {
        $this->stageId = $stageId;
        return $this;
    }

    public function getFiliere(): ?string
    {
        return $this->filiere;
    }

    public function setFiliere(string $filiere): self
    {
        $this->filiere = $filiere;
        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(?string $sujet): self
    {
        $this->sujet = $sujet;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getEtudiantId(): ?int
    {
        return $this->etudiantId;
    }

    public function setEtudiantId(int $etudiantId): self
    {
        $this->etudiantId = $etudiantId;
        return $this;
    }

    public function getEtudiantNom(): ?string
    {
        return $this->etudiantNom;
    }

    public function setEtudiantNom(string $etudiantNom): self
    {
        $this->etudiantNom = $etudiantNom;
        return $this;
    }

    public function getEtudiantPrenom(): ?string
    {
        return $this->etudiantPrenom;
    }

    public function setEtudiantPrenom(string $etudiantPrenom): self
    {
        $this->etudiantPrenom = $etudiantPrenom;
        return $this;
    }

    public function getEtudiantCode(): ?string
    {
        return $this->etudiantCode;
    }

    public function setEtudiantCode(string $etudiantCode): self
    {
        $this->etudiantCode = $etudiantCode;
        return $this;
    }

    public function getEncadrantId(): ?int
    {
        return $this->encadrantId;
    }

    public function setEncadrantId(?int $encadrantId): self
    {
        $this->encadrantId = $encadrantId;
        return $this;
    }

    public function getEncadrantNom(): ?string
    {
        return $this->encadrantNom;
    }

    public function setEncadrantNom(?string $encadrantNom): self
    {
        $this->encadrantNom = $encadrantNom;
        return $this;
    }

    public function getEncadrantPrenom(): ?string
    {
        return $this->encadrantPrenom;
    }

    public function setEncadrantPrenom(?string $encadrantPrenom): self
    {
        $this->encadrantPrenom = $encadrantPrenom;
        return $this;
    }

    public function getEncadrantRole(): ?string
    {
        return $this->encadrantRole;
    }

    public function setEncadrantRole(?string $encadrantRole): self
    {
        $this->encadrantRole = $encadrantRole;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}