<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutSuivi;
use App\Repository\SuiviUtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Abonnement d'un étudiant à un autre. Il attend l'accord de la personne
 * suivie ; un refus supprime la ligne, pour qu'une demande reste possible.
 */
#[ORM\Entity(repositoryClass: SuiviUtilisateurRepository::class)]
#[ORM\Table(name: 'follows_users')]
#[ORM\Index(name: 'idx_follows_demandes', columns: ['followed_id', 'statut'])]
#[ORM\Index(name: 'idx_follows_suivis', columns: ['follower_id', 'statut', 'followed_id'])]
class SuiviUtilisateur
{
    #[ORM\Column(length: 10, enumType: StatutSuivi::class, options: ['default' => 'pending'])]
    private StatutSuivi $statut = StatutSuivi::EnAttente;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'follower_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $abonne,

        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'followed_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $suivi,
    ) {
        if ($abonne === $suivi) {
            throw new \DomainException('On ne peut pas se suivre soi-même.');
        }

        $this->createdAt = new \DateTimeImmutable();

        // Un compte public accepte d'office : seul un compte privé filtre ses abonnés.
        if (!$suivi->isComptePrive()) {
            $this->accepter();
        }
    }

    public function getAbonne(): Utilisateur
    {
        return $this->abonne;
    }

    public function getSuivi(): Utilisateur
    {
        return $this->suivi;
    }

    public function getStatut(): StatutSuivi
    {
        return $this->statut;
    }

    public function estAccepte(): bool
    {
        return StatutSuivi::Accepte === $this->statut;
    }

    public function accepter(): void
    {
        $this->statut = StatutSuivi::Accepte;
        $this->respondedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }
}
