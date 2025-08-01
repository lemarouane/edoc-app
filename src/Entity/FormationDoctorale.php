<?php

namespace App\Entity;

use App\Repository\FormationDoctoraleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationDoctoraleRepository::class)]
#[ORM\Table(name: "formation_doctorale")]
class FormationDoctorale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $doctorant_id = null;

    #[ORM\Column(length: 255)]
    private ?string $doctorant_fullname = null;

    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Module $module = null;

    #[ORM\Column(length: 255)]
    private ?string $module_intitule = null;

    #[ORM\Column]
    private ?int $vol_horaire = null;

    #[ORM\Column(length: 255)]
    private ?string $intitule_formation = null;

    #[ORM\Column(length: 255)]
    private ?string $organisme_formation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(name: "piece_jointe", type: "string", length: 255, nullable: true)]
    private ?string $pieceJointe = null;

    #[ORM\Column(length: 50, options: ["default" => "En cours"])]
    private ?string $status = "En cours"; // New status field

    // Existing getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctorantId(): ?int
    {
        return $this->doctorant_id;
    }

    public function setDoctorantId(int $doctorant_id): self
    {
        $this->doctorant_id = $doctorant_id;
        return $this;
    }

    public function getDoctorantFullname(): ?string
    {
        return $this->doctorant_fullname;
    }

    public function setDoctorantFullname(string $doctorant_fullname): self
    {
        $this->doctorant_fullname = $doctorant_fullname;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getModuleIntitule(): ?string
    {
        return $this->module_intitule;
    }

    public function setModuleIntitule(string $module_intitule): self
    {
        $this->module_intitule = $module_intitule;
        return $this;
    }

    public function getVolHoraire(): ?int
    {
        return $this->vol_horaire;
    }

    public function setVolHoraire(int $vol_horaire): self
    {
        $this->vol_horaire = $vol_horaire;
        return $this;
    }

    public function getIntituleFormation(): ?string
    {
        return $this->intitule_formation;
    }

    public function setIntituleFormation(string $intitule_formation): self
    {
        $this->intitule_formation = $intitule_formation;
        return $this;
    }

    public function getOrganismeFormation(): ?string
    {
        return $this->organisme_formation;
    }

    public function setOrganismeFormation(string $organisme_formation): self
    {
        $this->organisme_formation = $organisme_formation;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getPieceJointe(): ?string
    {
        return $this->pieceJointe;
    }

    public function setPieceJointe(?string $pieceJointe): self
    {
        $this->pieceJointe = $pieceJointe;
        return $this;
    }

    // New status getters and setters
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}