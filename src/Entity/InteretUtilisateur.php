<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Un centre d'intérêt par ligne : l'annuaire classe les profils par goûts
 * communs, et seule une table indexée permet de le faire sans tout relire.
 */
#[ORM\Entity]
#[ORM\Table(name: 'user_interets')]
#[ORM\Index(name: 'k_interet', columns: ['interet', 'user_id'])]
class InteretUtilisateur
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(inversedBy: 'interets')]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,

        #[ORM\Id]
        #[ORM\Column(length: 40)]
        private string $interet,
    ) {
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getInteret(): string
    {
        return $this->interet;
    }
}
