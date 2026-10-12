<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TypeLieu;
use App\Repository\EtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Lieu partenaire (bar, boîte, restaurant, afterwork), rattaché à un compte partenaire. */
#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
#[ORM\Table(name: 'etablissements')]
#[ORM\Index(name: 'idx_etab_ville', columns: ['ville'])]
class Etablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private Utilisateur $proprietaire;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private string $nom = '';

    #[ORM\Column(length: 20, enumType: TypeLieu::class)]
    private TypeLieu $type = TypeLieu::Bar;

    #[ORM\Column(nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: true, options: ['default' => 'Clermont-Ferrand'])]
    private ?string $ville = 'Clermont-Ferrand';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    /** @var Collection<int, Evenement> */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'etablissement')]
    #[ORM\OrderBy(['dateHeure' => 'ASC'])]
    private Collection $evenements;

    /** @var Collection<int, EtablissementPhoto> */
    #[ORM\OneToMany(targetEntity: EtablissementPhoto::class, mappedBy: 'etablissement', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $photos;

    public function __construct(Utilisateur $proprietaire)
    {
        $this->proprietaire = $proprietaire;
        $this->evenements = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProprietaire(): Utilisateur
    {
        return $this->proprietaire;
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

    public function getType(): TypeLieu
    {
        return $this->type;
    }

    public function setType(TypeLieu $type): static
    {
        $this->type = $type;

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

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return null === $this->latitude ? null : (float) $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return null === $this->longitude ? null : (float) $this->longitude;
    }

    public function placerSurLaCarte(?float $latitude, ?float $longitude): static
    {
        $this->latitude = null === $latitude ? null : (string) $latitude;
        $this->longitude = null === $longitude ? null : (string) $longitude;

        return $this;
    }

    /** @return Collection<int, Evenement> */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    /** @return Collection<int, EtablissementPhoto> */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }
}
