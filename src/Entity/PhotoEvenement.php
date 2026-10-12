<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo postée pendant une soirée par un participant scanné à l'entrée.
 * Une seule par personne et par soirée : reposter remplace la précédente.
 */
#[ORM\Entity]
#[ORM\Table(name: 'evenement_photos')]
#[ORM\UniqueConstraint(name: 'unique_photo_par_personne', columns: ['evenement_id', 'user_id'])]
#[ORM\Index(name: 'idx_evphoto_event', columns: ['evenement_id', 'created_at'])]
class PhotoEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $masquee = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Evenement $evenement,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $auteur,

        #[ORM\Column]
        private string $fichier,

        #[ORM\Column(nullable: true)]
        private ?string $legende = null,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): Evenement
    {
        return $this->evenement;
    }

    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    public function getFichier(): string
    {
        return $this->fichier;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function isMasquee(): bool
    {
        return $this->masquee;
    }

    public function masquer(): void
    {
        $this->masquee = true;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
