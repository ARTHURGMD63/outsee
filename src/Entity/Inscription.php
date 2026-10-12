<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutInscription;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

/** Réservation d'un étudiant à un événement, avec le QR code présenté à l'entrée. */
#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\Table(name: 'inscriptions')]
#[ORM\UniqueConstraint(name: 'unique_inscription', columns: ['user_id', 'evenement_id'])]
#[ORM\UniqueConstraint(name: 'idx_insc_qr', columns: ['qr_code'])]
#[ORM\Index(name: 'idx_insc_ev_statut', columns: ['evenement_id', 'statut'])]
#[ORM\Index(name: 'idx_insc_user_statut_date', columns: ['user_id', 'statut', 'created_at'])]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Jeton aléatoire : deviner le QR d'un autre étudiant est impossible. */
    #[ORM\Column(length: 64)]
    private string $qrCode;

    #[ORM\Column(length: 10, enumType: StatutInscription::class, options: ['default' => 'inscrit'])]
    private StatutInscription $statut = StatutInscription::Inscrit;

    /** Instant du scan à l'entrée, distinct de l'instant de la réservation. */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $checkinLe = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,

        #[ORM\ManyToOne(inversedBy: 'inscriptions')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Evenement $evenement,
    ) {
        $this->qrCode = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getEvenement(): Evenement
    {
        return $this->evenement;
    }

    public function getQrCode(): string
    {
        return $this->qrCode;
    }

    public function getStatut(): StatutInscription
    {
        return $this->statut;
    }

    public function estActive(): bool
    {
        return StatutInscription::Annule !== $this->statut;
    }

    public function annuler(): void
    {
        $this->statut = StatutInscription::Annule;
    }

    /** Réactiver une réservation annulée lui donne un nouveau QR code. */
    public function reactiver(): void
    {
        $this->statut = StatutInscription::Inscrit;
        $this->qrCode = bin2hex(random_bytes(32));
        $this->checkinLe = null;
    }

    public function validerEntree(\DateTimeImmutable $maintenant = new \DateTimeImmutable()): void
    {
        $this->statut = StatutInscription::Checkin;
        $this->checkinLe = $maintenant;
    }

    public function getCheckinLe(): ?\DateTimeImmutable
    {
        return $this->checkinLe;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
