<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\FormuleSponsoring;
use App\Enum\StyleMusique;
use App\Enum\TypeLieu;
use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenements')]
#[ORM\Index(name: 'idx_ev_date', columns: ['date_heure'])]
#[ORM\Index(name: 'idx_ev_etab_date', columns: ['etablissement_id', 'date_heure'])]
#[ORM\Index(name: 'idx_ev_type_date', columns: ['type', 'date_heure'])]
#[ORM\Index(name: 'idx_ev_etab_creation', columns: ['etablissement_id', 'created_at'])]
#[ORM\Index(name: 'idx_sponsor', columns: ['is_sponsorise', 'sponsor_jusqu_au'])]
#[ORM\Index(name: 'idx_style_musique', columns: ['style_musique', 'date_heure'])]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Etablissement $etablissement;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $titre = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: TypeLieu::class)]
    private TypeLieu $type = TypeLieu::Bar;

    #[ORM\Column(length: 20, nullable: true, enumType: StyleMusique::class)]
    private ?StyleMusique $styleMusique = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private \DateTimeImmutable $dateHeure;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'dateHeure', message: 'La fin doit suivre le début.')]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(options: ['default' => 100])]
    #[Assert\Positive]
    private int $quota = 100;

    /** Réduction en pourcentage accordée aux membres Outsee. */
    #[ORM\Column(options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 100)]
    private int $reduction = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => '0.00'])]
    #[Assert\PositiveOrZero]
    private string $prixNormal = '0.00';

    #[ORM\Column(name: 'is_flash', options: ['default' => false])]
    private bool $flash = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $flashExpiry = null;

    #[ORM\Column(name: 'is_gratuit', options: ['default' => false])]
    private bool $gratuit = false;

    #[ORM\Column(name: 'is_sponsorise', options: ['default' => false])]
    private bool $sponsorise = false;

    #[ORM\Column(length: 20, nullable: true, enumType: FormuleSponsoring::class)]
    private ?FormuleSponsoring $sponsorFormule = null;

    /** Tarif figé au moment de l'achat. */
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => '0.00'])]
    private string $sponsorTarif = '0.00';

    #[ORM\Column(name: 'sponsor_jusqu_au', nullable: true)]
    private ?\DateTimeImmutable $sponsorJusquAu = null;

    #[ORM\Column(nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Inscription> */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'evenement')]
    private Collection $inscriptions;

    public function __construct(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
        $this->type = $etablissement->getType();
        $this->lieu = $etablissement->getAdresse();
        $this->dateHeure = new \DateTimeImmutable('+1 day 20:00');
        $this->createdAt = new \DateTimeImmutable();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = trim($titre);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): TypeLieu
    {
        return $this->type;
    }

    public function setType(TypeLieu $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStyleMusique(): ?StyleMusique
    {
        return $this->styleMusique;
    }

    public function setStyleMusique(?StyleMusique $styleMusique): static
    {
        $this->styleMusique = $styleMusique;

        return $this;
    }

    public function getDateHeure(): \DateTimeImmutable
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTimeImmutable $dateHeure): static
    {
        $this->dateHeure = $dateHeure;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getQuota(): int
    {
        return $this->quota;
    }

    public function setQuota(int $quota): static
    {
        $this->quota = $quota;

        return $this;
    }

    public function getReduction(): int
    {
        return $this->reduction;
    }

    public function setReduction(int $reduction): static
    {
        $this->reduction = $reduction;

        return $this;
    }

    public function getPrixNormal(): float
    {
        return (float) $this->prixNormal;
    }

    public function setPrixNormal(float $prixNormal): static
    {
        $this->prixNormal = number_format($prixNormal, 2, '.', '');

        return $this;
    }

    /** Prix payé par un membre, réduction déduite. */
    public function getPrixMembre(): float
    {
        if ($this->gratuit) {
            return 0.0;
        }

        return round($this->getPrixNormal() * (100 - $this->reduction) / 100, 2);
    }

    /** Ce qu'un membre économise en venant avec son pass. */
    public function getEconomie(): float
    {
        return round($this->getPrixNormal() - $this->getPrixMembre(), 2);
    }

    public function isFlash(): bool
    {
        return $this->flash;
    }

    public function setFlash(bool $flash, ?\DateTimeImmutable $expiration = null): static
    {
        $this->flash = $flash;
        $this->flashExpiry = $flash ? $expiration : null;

        return $this;
    }

    public function getFlashExpiry(): ?\DateTimeImmutable
    {
        return $this->flashExpiry;
    }

    public function isGratuit(): bool
    {
        return $this->gratuit;
    }

    public function setGratuit(bool $gratuit): static
    {
        $this->gratuit = $gratuit;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSponsorFormule(): ?FormuleSponsoring
    {
        return $this->sponsorFormule;
    }

    public function getSponsorTarif(): float
    {
        return (float) $this->sponsorTarif;
    }

    public function getSponsorJusquAu(): ?\DateTimeImmutable
    {
        return $this->sponsorJusquAu;
    }

    /**
     * La mise en avant court à partir de l'achat, sans jamais dépasser la
     * date de l'événement : pousser une soirée passée n'aurait aucun sens.
     */
    public function sponsoriser(FormuleSponsoring $formule, \DateTimeImmutable $maintenant = new \DateTimeImmutable()): void
    {
        $fin = $maintenant->modify(\sprintf('+%d hours', $formule->dureeEnHeures()));

        $this->sponsorise = true;
        $this->sponsorFormule = $formule;
        $this->sponsorTarif = number_format($formule->tarif(), 2, '.', '');
        $this->sponsorJusquAu = min($fin, $this->dateHeure);
    }

    public function isSponsoriseActif(\DateTimeImmutable $maintenant = new \DateTimeImmutable()): bool
    {
        return $this->sponsorise && (null === $this->sponsorJusquAu || $this->sponsorJusquAu > $maintenant);
    }

    public function estPasse(\DateTimeImmutable $maintenant = new \DateTimeImmutable()): bool
    {
        return ($this->dateFin ?? $this->dateHeure->modify('+6 hours')) < $maintenant;
    }

    /** @return Collection<int, Inscription> */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }
}
