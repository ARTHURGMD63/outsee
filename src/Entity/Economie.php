<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/** Montant économisé par un étudiant grâce à la réduction d'un événement. */
#[ORM\Entity]
#[ORM\Table(name: 'economies')]
#[ORM\Index(name: 'idx_eco_user_date', columns: ['user_id', 'date_economie'])]
class Economie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => '0.00'])]
    private string $montant;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateEconomie;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
        private Utilisateur $utilisateur,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Evenement $evenement,

        float $montant,
    ) {
        $this->montant = number_format($montant, 2, '.', '');
        $this->dateEconomie = new \DateTimeImmutable('today');
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

    public function getMontant(): float
    {
        return (float) $this->montant;
    }

    public function getDateEconomie(): \DateTimeImmutable
    {
        return $this->dateEconomie;
    }
}
