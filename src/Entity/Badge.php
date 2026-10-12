<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Badge du catalogue, identifié par un code stable (« first_event »). */
#[ORM\Entity]
#[ORM\Table(name: 'badges')]
class Badge
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 50)]
        private string $code,

        #[ORM\Column(length: 100)]
        private string $nom,

        #[ORM\Column(nullable: true)]
        private ?string $description = null,

        /** Nom d'une icône du jeu d'icônes de l'interface. */
        #[ORM\Column(length: 32, nullable: true)]
        private ?string $icon = null,

        #[ORM\Column(length: 40, nullable: true)]
        private ?string $couleur = null,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }
}
