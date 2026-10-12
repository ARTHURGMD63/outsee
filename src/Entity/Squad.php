<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NiveauSquad;
use App\Enum\TypeSquad;
use App\Repository\SquadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Sortie sportive ou culturelle proposée par un étudiant. */
#[ORM\Entity(repositoryClass: SquadRepository::class)]
#[ORM\Table(name: 'squads')]
#[ORM\Index(name: 'idx_sq_date', columns: ['date_heure'])]
class Squad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Utilisateur $createur;

    #[ORM\Column(length: 20, enumType: TypeSquad::class)]
    private TypeSquad $type = TypeSquad::Running;

    #[ORM\Column(options: ['default' => false])]
    private bool $creeParAssociation = false;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $titre = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 10, enumType: NiveauSquad::class, options: ['default' => 'tous'])]
    private NiveauSquad $niveau = NiveauSquad::Tous;

    #[ORM\Column]
    private \DateTimeImmutable $dateHeure;

    #[ORM\Column(nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(options: ['default' => 10])]
    #[Assert\Range(min: 2, max: 100)]
    private int $quota = 10;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, SquadMembre> */
    #[ORM\OneToMany(targetEntity: SquadMembre::class, mappedBy: 'squad', cascade: ['persist'], orphanRemoval: true)]
    private Collection $membres;

    public function __construct(Utilisateur $createur)
    {
        $this->createur = $createur;
        $this->dateHeure = new \DateTimeImmutable('+1 day 18:00');
        $this->createdAt = new \DateTimeImmutable();
        $this->membres = new ArrayCollection();
        $this->membres->add(new SquadMembre($this, $createur));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateur(): Utilisateur
    {
        return $this->createur;
    }

    public function getType(): TypeSquad
    {
        return $this->type;
    }

    public function setType(TypeSquad $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isCreeParAssociation(): bool
    {
        return $this->creeParAssociation;
    }

    public function setCreeParAssociation(bool $creeParAssociation): static
    {
        $this->creeParAssociation = $creeParAssociation;

        return $this;
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

    public function getNiveau(): NiveauSquad
    {
        return $this->niveau;
    }

    public function setNiveau(NiveauSquad $niveau): static
    {
        $this->niveau = $niveau;

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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, SquadMembre> */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function estMembre(Utilisateur $utilisateur): bool
    {
        return $this->membres->exists(static fn (int $i, SquadMembre $m): bool => $m->getUtilisateur() === $utilisateur);
    }

    public function estComplete(): bool
    {
        return $this->membres->count() >= $this->quota;
    }

    public function rejoindre(Utilisateur $utilisateur): void
    {
        if ($this->estMembre($utilisateur)) {
            return;
        }
        if ($this->estComplete()) {
            throw new \DomainException('Cette squad est complète.');
        }

        $this->membres->add(new SquadMembre($this, $utilisateur));
    }

    public function quitter(Utilisateur $utilisateur): void
    {
        if ($utilisateur === $this->createur) {
            throw new \DomainException('Le créateur ne peut pas quitter sa squad.');
        }

        foreach ($this->membres as $membre) {
            if ($membre->getUtilisateur() === $utilisateur) {
                $this->membres->removeElement($membre);
            }
        }
    }
}
