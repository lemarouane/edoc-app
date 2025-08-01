<?php

namespace App\Entity;

use App\Repository\InscritEtudiantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscritEtudiantRepository::class)]
#[ORM\Table(name: 'inscritetudiant')] 
class InscritEtudiant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    #[ORM\ManyToOne(inversedBy: 'inscritEtudiants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EtudiantDD $inscription = null;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getInscription(): ?EtudiantDD
    {
        return $this->inscription;
    }

    public function setInscription(?EtudiantDD $inscription): self
    {
        $this->inscription = $inscription;

        return $this;
    }
}
