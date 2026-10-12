<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Un compte bloqué ne voit plus le profil ni l'activité de celui qui l'a bloqué. */
#[ORM\Entity]
#[ORM\Table(name: 'user_blocks')]
#[ORM\Index(name: 'k_blocked', columns: ['blocked_id'])]
class Blocage
{
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'blocker_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $auteur,

        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'blocked_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $bloque,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    public function getBloque(): Utilisateur
    {
        return $this->bloque;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
