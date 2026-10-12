<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Un étudiant suit un lieu pour voir passer ses prochaines soirées. */
#[ORM\Entity]
#[ORM\Table(name: 'follows_etablissements')]
class SuiviEtablissement
{
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,

        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Etablissement $etablissement,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
