<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $doctorant_id = null; // From another database

    #[ORM\Column(length: 255)]
    private ?string $doctorant_fullname = null; // Moved right after doctorant_id

    #[ORM\ManyToOne(targetEntity: Niveau::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Niveau $niveau = null; // Keep the relationship

    #[ORM\Column(length: 255)]
    private ?string $niveau_intitule = null; // Store the intitule but keep the relation

    #[ORM\Column]
    private ?int $annee = null; // Year

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $piecejointe = null; // Optional attachment

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

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(Niveau $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getNiveauIntitule(): ?string
    {
        return $this->niveau_intitule;
    }

    public function setNiveauIntitule(string $niveau_intitule): self
    {
        $this->niveau_intitule = $niveau_intitule;
        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;
        return $this;
    }

    public function getPiecejointe(): ?string
    {
        return $this->piecejointe;
    }

    public function setPiecejointe(?string $piecejointe): self
    {
        $this->piecejointe = $piecejointe;
        return $this;
    }
}
