<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Photo de la galerie d'un établissement, dans l'ordre choisi par le partenaire. */
#[ORM\Entity]
#[ORM\Table(name: 'etablissement_photos')]
#[ORM\Index(name: 'idx_etab_position', columns: ['etablissement_id', 'position'])]
class EtablissementPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'photos')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Etablissement $etablissement,

        #[ORM\Column]
        private string $fichier,

        #[ORM\Column(length: 160, nullable: true)]
        private ?string $legende = null,

        #[ORM\Column(options: ['default' => 0])]
        private int $position = 0,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    public function getFichier(): string
    {
        return $this->fichier;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
