<?php

namespace App\Entity;

use App\Repository\ChoixAffecterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChoixAffecterRepository::class)]
#[ORM\Table(name: 'choixaffecter')] 
class ChoixAffecter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $affectation = null;

    #[ORM\OneToOne(inversedBy: 'choixaffecter', cascade: ['persist', 'remove'])]
    private ?Etat $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAffectation(): ?string
    {
        return $this->affectation;
    }

    public function setAffectation(string $affectation): self
    {
        $this->affectation = $affectation;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
