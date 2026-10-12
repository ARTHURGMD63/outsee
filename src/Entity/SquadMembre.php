<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'squad_membres')]
#[ORM\UniqueConstraint(name: 'unique_membre', columns: ['squad_id', 'user_id'])]
#[ORM\Index(name: 'idx_sm_user_date', columns: ['user_id', 'joined_at'])]
class SquadMembre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $joinedAt;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'membres')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Squad $squad,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,
    ) {
        $this->joinedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSquad(): Squad
    {
        return $this->squad;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getJoinedAt(): \DateTimeImmutable
    {
        return $this->joinedAt;
    }
}
