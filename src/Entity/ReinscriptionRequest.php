<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "reinscription_request")]
class ReinscriptionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "directeur_these_nom_prenom", type: "string", length: 255)]
    private ?string $directeurTheseNomPrenom = null;

    #[ORM\Column(type: "integer", name: "doctorant_id")]
    private ?int $doctorantId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDirecteurTheseNomPrenom(): ?string
    {
        return $this->directeurTheseNomPrenom;
    }

    public function setDirecteurTheseNomPrenom(string $directeurTheseNomPrenom): self
    {
        $this->directeurTheseNomPrenom = $directeurTheseNomPrenom;
        return $this;
    }

    public function getDoctorantId(): ?int
    {
        return $this->doctorantId;
    }

    public function setDoctorantId(int $doctorantId): self
    {
        $this->doctorantId = $doctorantId;
        return $this;
    }
}