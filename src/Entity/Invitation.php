<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutInvitation;
use App\Enum\TypeInvitation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Invitation d'un ami à un événement ou à une squad. La cible est désignée
 * par son type et son identifiant ; la contrainte d'unicité empêche
 * d'envoyer deux fois la même invitation.
 */
#[ORM\Entity]
#[ORM\Table(name: 'invitations')]
#[ORM\UniqueConstraint(name: 'uniq_invitation', columns: ['from_user_id', 'to_user_id', 'type', 'target_id'])]
#[ORM\Index(name: 'k_to', columns: ['to_user_id', 'statut'])]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, enumType: StatutInvitation::class, options: ['default' => 'pending'])]
    private StatutInvitation $statut = StatutInvitation::EnAttente;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'from_user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $expediteur,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'to_user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $destinataire,

        #[ORM\Column(length: 10, enumType: TypeInvitation::class)]
        private TypeInvitation $type,

        #[ORM\Column]
        private int $targetId,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpediteur(): Utilisateur
    {
        return $this->expediteur;
    }

    public function getDestinataire(): Utilisateur
    {
        return $this->destinataire;
    }

    public function getType(): TypeInvitation
    {
        return $this->type;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }

    public function getStatut(): StatutInvitation
    {
        return $this->statut;
    }

    public function repondre(bool $accepte): void
    {
        $this->statut = $accepte ? StatutInvitation::Acceptee : StatutInvitation::Refusee;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
