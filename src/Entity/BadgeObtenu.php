<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_badges')]
class BadgeObtenu
{
    #[ORM\Column]
    private \DateTimeImmutable $unlockedAt;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,

        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'badge_code', referencedColumnName: 'code', nullable: false, onDelete: 'CASCADE')]
        private Badge $badge,
    ) {
        $this->unlockedAt = new \DateTimeImmutable();
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getBadge(): Badge
    {
        return $this->badge;
    }

    public function getUnlockedAt(): \DateTimeImmutable
    {
        return $this->unlockedAt;
    }
}
