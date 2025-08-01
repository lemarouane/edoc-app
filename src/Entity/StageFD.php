<?php

namespace App\Entity;

use App\Repository\StageFDRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StageFDRepository::class)]
#[ORM\Table(name: "stage_fd")]
class StageFD
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "integer", name: "doctorant_id")]
    private int $doctorantId;

    #[ORM\Column(type: "string", length: 255, name: "doctorant_fullname")]
    private string $doctorantFullname;

    #[ORM\Column(type: "integer", name: "module_id")]
    private int $moduleId;

    #[ORM\Column(type: "string", length: 255, name: "cadre_stage")]
    private string $cadreStage;

    #[ORM\Column(type: "date", name: "date_debut")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date", name: "date_fin")]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: "string", length: 255, nullable: true, name: "lettre_acceptation")]
    private ?string $lettreAcceptation = null;

    #[ORM\Column(type: "string", length: 255, name: "lieu_stage")]
    private string $lieuStage;

    #[ORM\Column(type: "string", length: 255, name: "entite_hebergante")]
    private string $entiteHebergante;

    #[ORM\Column(type: "string", length: 50, name: "status")]
    private string $status = 'En cours';

    // Getters and Setters

    public function getId(): ?int
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

    public function getDoctorantFullname(): string
    {
        return $this->doctorantFullname;
    }

    public function setDoctorantFullname(string $doctorantFullname): self
    {
        $this->doctorantFullname = $doctorantFullname;
        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): self
    {
        $this->moduleId = $moduleId;
        return $this;
    }

    public function getCadreStage(): string
    {
        return $this->cadreStage;
    }

    public function setCadreStage(string $cadreStage): self
    {
        $this->cadreStage = $cadreStage;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): \DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getLettreAcceptation(): ?string
    {
        return $this->lettreAcceptation;
    }

    public function setLettreAcceptation(?string $lettreAcceptation): self
    {
        $this->lettreAcceptation = $lettreAcceptation;
        return $this;
    }

    public function getLieuStage(): string
    {
        return $this->lieuStage;
    }

    public function setLieuStage(string $lieuStage): self
    {
        $this->lieuStage = $lieuStage;
        return $this;
    }

    public function getEntiteHebergante(): string
    {
        return $this->entiteHebergante;
    }

    public function setEntiteHebergante(string $entiteHebergante): self
    {
        $this->entiteHebergante = $entiteHebergante;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}