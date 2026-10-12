<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MotifSignalement;
use App\Enum\StatutSignalement;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Signalement d'un compte, traité par la modération. */
#[ORM\Entity]
#[ORM\Table(name: 'user_reports')]
#[ORM\Index(name: 'k_reported', columns: ['reported_id', 'statut'])]
class Signalement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, enumType: StatutSignalement::class, options: ['default' => 'nouveau'])]
    private StatutSignalement $statut = StatutSignalement::Nouveau;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'traite_par', nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $traitePar = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $traiteLe = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'reporter_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $auteur,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'reported_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $signale,

        #[ORM\Column(length: 30, enumType: MotifSignalement::class)]
        private MotifSignalement $motif,

        #[ORM\Column(length: 500, nullable: true)]
        #[Assert\Length(max: 500)]
        private ?string $details = null,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    public function getSignale(): Utilisateur
    {
        return $this->signale;
    }

    public function getMotif(): MotifSignalement
    {
        return $this->motif;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getStatut(): StatutSignalement
    {
        return $this->statut;
    }

    public function traiter(Utilisateur $moderateur): void
    {
        $this->statut = StatutSignalement::Traite;
        $this->traitePar = $moderateur;
        $this->traiteLe = new \DateTimeImmutable();
    }

    public function getTraitePar(): ?Utilisateur
    {
        return $this->traitePar;
    }

    public function getTraiteLe(): ?\DateTimeImmutable
    {
        return $this->traiteLe;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
