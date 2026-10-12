<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CategorieClient;
use App\Enum\OffreClient;
use App\Enum\StatutClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compte commercial du back-office. Distinct de l'établissement : un
 * prospect existe avant d'avoir un compte dans l'application, et le lien
 * se fait le jour où le partenaire s'inscrit.
 */
#[ORM\Entity]
#[ORM\Table(name: 'crm_clients')]
#[ORM\Index(name: 'k_statut', columns: ['statut'])]
#[ORM\HasLifecycleCallbacks]
class ClientCrm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 191)]
    #[Assert\NotBlank]
    private string $nom = '';

    #[ORM\Column(length: 20, enumType: CategorieClient::class, options: ['default' => 'bar'])]
    private CategorieClient $categorie = CategorieClient::Bar;

    #[ORM\Column(length: 100, options: ['default' => 'Clermont-Ferrand'])]
    private string $ville = 'Clermont-Ferrand';

    #[ORM\Column(nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $contactNom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactRole = null;

    #[ORM\Column(length: 191, nullable: true)]
    #[Assert\Email]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $contactTel = null;

    #[ORM\Column(length: 20, enumType: StatutClient::class, options: ['default' => 'prospect'])]
    private StatutClient $statut = StatutClient::Prospect;

    #[ORM\Column(length: 20, enumType: OffreClient::class, options: ['default' => 'aucune'])]
    private OffreClient $offre = OffreClient::Aucune;

    /** Montant mensuel HT facturé, figé à la signature. */
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, options: ['default' => '0.00'])]
    private string $mrr = '0.00';

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $essaiJusquAu = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $signeLe = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $perduLe = null;

    #[ORM\Column(nullable: true)]
    private ?string $motifPerte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $responsable = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, InteractionCrm> */
    #[ORM\OneToMany(targetEntity: InteractionCrm::class, mappedBy: 'client', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $interactions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->interactions = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function toucher(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function depuisEtablissement(Etablissement $etablissement): self
    {
        $client = new self();
        $client->etablissement = $etablissement;
        $client->nom = $etablissement->getNom();
        $client->categorie = CategorieClient::from($etablissement->getType()->value);
        $client->ville = $etablissement->getVille() ?? 'Clermont-Ferrand';
        $client->adresse = $etablissement->getAdresse();

        return $client;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = trim($nom);

        return $this;
    }

    public function getCategorie(): CategorieClient
    {
        return $this->categorie;
    }

    public function setCategorie(CategorieClient $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getContactNom(): ?string
    {
        return $this->contactNom;
    }

    public function setContactNom(?string $contactNom): static
    {
        $this->contactNom = $contactNom;

        return $this;
    }

    public function getContactRole(): ?string
    {
        return $this->contactRole;
    }

    public function setContactRole(?string $contactRole): static
    {
        $this->contactRole = $contactRole;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactTel(): ?string
    {
        return $this->contactTel;
    }

    public function setContactTel(?string $contactTel): static
    {
        $this->contactTel = $contactTel;

        return $this;
    }

    public function getStatut(): StatutClient
    {
        return $this->statut;
    }

    public function setStatut(StatutClient $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getOffre(): OffreClient
    {
        return $this->offre;
    }

    public function getMrr(): float
    {
        return (float) $this->mrr;
    }

    /** Signature : l'offre et son montant sont figés dans le contrat. */
    public function signer(OffreClient $offre, float $mrr, \DateTimeImmutable $le = new \DateTimeImmutable('today')): void
    {
        $this->offre = $offre;
        $this->mrr = number_format($mrr, 2, '.', '');
        $this->statut = StatutClient::Actif;
        $this->signeLe = $le;
        $this->perduLe = null;
        $this->motifPerte = null;
    }

    public function demarrerEssai(OffreClient $offre, \DateTimeImmutable $jusquAu): void
    {
        $this->offre = $offre;
        $this->statut = StatutClient::Essai;
        $this->essaiJusquAu = $jusquAu;
    }

    public function marquerPerdu(?string $motif, \DateTimeImmutable $le = new \DateTimeImmutable('today')): void
    {
        $this->statut = StatutClient::Perdu;
        $this->perduLe = $le;
        $this->motifPerte = $motif;
        $this->mrr = '0.00';
    }

    public function getEssaiJusquAu(): ?\DateTimeImmutable
    {
        return $this->essaiJusquAu;
    }

    public function getSigneLe(): ?\DateTimeImmutable
    {
        return $this->signeLe;
    }

    public function getPerduLe(): ?\DateTimeImmutable
    {
        return $this->perduLe;
    }

    public function getMotifPerte(): ?string
    {
        return $this->motifPerte;
    }

    public function getResponsable(): ?Utilisateur
    {
        return $this->responsable;
    }

    public function setResponsable(?Utilisateur $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, InteractionCrm> */
    public function getInteractions(): Collection
    {
        return $this->interactions;
    }

    /** L'accès partenaire est ouvert pendant l'essai et une fois le contrat actif. */
    public function donneAcces(\DateTimeImmutable $aujourdhui = new \DateTimeImmutable('today')): bool
    {
        return match ($this->statut) {
            StatutClient::Actif => true,
            StatutClient::Essai => null === $this->essaiJusquAu || $this->essaiJusquAu >= $aujourdhui,
            default => false,
        };
    }
}
