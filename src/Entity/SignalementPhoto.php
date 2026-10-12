<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MotifSignalementPhoto;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'evenement_photo_signalements')]
#[ORM\UniqueConstraint(name: 'unique_signalement', columns: ['photo_id', 'reporter_id'])]
class SignalementPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'photo_id', nullable: false, onDelete: 'CASCADE')]
        private PhotoEvenement $photo,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'reporter_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $auteur,

        #[ORM\Column(length: 30, enumType: MotifSignalementPhoto::class)]
        private MotifSignalementPhoto $motif,

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

    public function getPhoto(): PhotoEvenement
    {
        return $this->photo;
    }

    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    public function getMotif(): MotifSignalementPhoto
    {
        return $this->motif;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
