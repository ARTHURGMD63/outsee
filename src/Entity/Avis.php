<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Note laissée après une soirée où l'étudiant a bien été scanné à l'entrée. */
#[ORM\Entity]
#[ORM\Table(name: 'avis')]
#[ORM\UniqueConstraint(name: 'unique_avis', columns: ['user_id', 'evenement_id'])]
#[ORM\Index(name: 'idx_avis_evenement', columns: ['evenement_id', 'note'])]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $auteur,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Evenement $evenement,

        #[ORM\Column(type: Types::SMALLINT)]
        #[Assert\Range(min: 1, max: 5)]
        private int $note,

        #[ORM\Column(type: Types::TEXT, nullable: true)]
        #[Assert\Length(max: 1000)]
        private ?string $commentaire = null,
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

    public function getEvenement(): Evenement
    {
        return $this->evenement;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
