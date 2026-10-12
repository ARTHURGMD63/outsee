<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CategorieMouvement;
use App\Enum\SensMouvement;
use App\Enum\StatutMouvement;
use App\Repository\MouvementFinancierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ligne du registre financier. Le MRR se déduit des abonnements, la
 * trésorerie seulement de ce registre.
 */
#[ORM\Entity(repositoryClass: MouvementFinancierRepository::class)]
#[ORM\Table(name: 'finance_mouvements')]
#[ORM\Index(name: 'k_periode', columns: ['date_mouvement', 'sens'])]
class MouvementFinancier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, enumType: SensMouvement::class)]
    private SensMouvement $sens = SensMouvement::Recette;

    #[ORM\Column(length: 20, enumType: CategorieMouvement::class)]
    private CategorieMouvement $categorie = CategorieMouvement::Abonnement;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ClientCrm $client = null;

    #[ORM\Column(length: 191)]
    #[Assert\NotBlank]
    private string $libelle = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    private string $montantHt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '20.00'])]
    private string $tvaTaux = '20.00';

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateMouvement;

    #[ORM\Column(length: 10, enumType: StatutMouvement::class, options: ['default' => 'regle'])]
    private StatutMouvement $statut = StatutMouvement::Regle;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $moyen = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'cree_par', nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $creePar = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->dateMouvement = new \DateTimeImmutable('today');
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSens(): SensMouvement
    {
        return $this->sens;
    }

    public function setSens(SensMouvement $sens): static
    {
        $this->sens = $sens;

        return $this;
    }

    public function getCategorie(): CategorieMouvement
    {
        return $this->categorie;
    }

    public function setCategorie(CategorieMouvement $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getClient(): ?ClientCrm
    {
        return $this->client;
    }

    public function setClient(?ClientCrm $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = trim($libelle);

        return $this;
    }

    public function getMontantHt(): float
    {
        return (float) $this->montantHt;
    }

    public function setMontantHt(float $montantHt): static
    {
        $this->montantHt = number_format($montantHt, 2, '.', '');

        return $this;
    }

    public function getTvaTaux(): float
    {
        return (float) $this->tvaTaux;
    }

    public function setTvaTaux(float $tvaTaux): static
    {
        $this->tvaTaux = number_format($tvaTaux, 2, '.', '');

        return $this;
    }

    public function getMontantTtc(): float
    {
        return round($this->getMontantHt() * (1 + $this->getTvaTaux() / 100), 2);
    }

    public function getDateMouvement(): \DateTimeImmutable
    {
        return $this->dateMouvement;
    }

    public function setDateMouvement(\DateTimeImmutable $dateMouvement): static
    {
        $this->dateMouvement = $dateMouvement;

        return $this;
    }

    public function getStatut(): StatutMouvement
    {
        return $this->statut;
    }

    public function setStatut(StatutMouvement $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMoyen(): ?string
    {
        return $this->moyen;
    }

    public function setMoyen(?string $moyen): static
    {
        $this->moyen = $moyen;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getCreePar(): ?Utilisateur
    {
        return $this->creePar;
    }

    public function setCreePar(?Utilisateur $creePar): static
    {
        $this->creePar = $creePar;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
