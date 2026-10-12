<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Theme;
use App\Enum\TypeCompte;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_users_type_ecole', columns: ['type', 'ecole'])]
#[ORM\Index(name: 'idx_users_type_date', columns: ['type', 'created_at'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $nom = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $prenom = '';

    #[ORM\Column(length: 191, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 191)]
    private string $email = '';

    /** Empreinte du mot de passe, jamais le mot de passe en clair. */
    #[ORM\Column]
    private string $password = '';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ecole = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $promo = null;

    /** Ville choisie à l'inscription, distincte de celle d'un établissement. */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    /** Nom du fichier de la photo de profil ; null affiche l'initiale. */
    #[ORM\Column(nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateNaissance = null;

    /** Preuve de l'acceptation des CGU : une case cochée sans trace ne prouve rien. */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cguAccepteesLe = null;

    #[ORM\Column(length: 20, enumType: TypeCompte::class, options: ['default' => 'etudiant'])]
    private TypeCompte $type = TypeCompte::Etudiant;

    /** Un compte privé ne montre son activité qu'aux abonnés acceptés. */
    #[ORM\Column(options: ['default' => true])]
    private bool $comptePrive = true;

    #[ORM\Column(length: 10, enumType: Theme::class, options: ['default' => 'light'])]
    private Theme $theme = Theme::Clair;

    /** Instant du dernier « Tout lire » : les notifications plus anciennes sont lues. */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $notificationsLuesLe = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, InteretUtilisateur> */
    #[ORM\OneToMany(targetEntity: InteretUtilisateur::class, mappedBy: 'utilisateur', cascade: ['persist'], orphanRemoval: true)]
    private Collection $interets;

    /** @var Collection<int, Etablissement> */
    #[ORM\OneToMany(targetEntity: Etablissement::class, mappedBy: 'proprietaire')]
    private Collection $etablissements;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->interets = new ArrayCollection();
        $this->etablissements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = trim($prenom);

        return $this;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function getInitiale(): string
    {
        return mb_strtoupper(mb_substr($this->prenom, 0, 1));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return match ($this->type) {
            TypeCompte::Etudiant => ['ROLE_USER', 'ROLE_ETUDIANT'],
            TypeCompte::Partenaire => ['ROLE_USER', 'ROLE_PARTENAIRE'],
            TypeCompte::Admin => ['ROLE_USER', 'ROLE_ADMIN'],
            TypeCompte::Supprime => [],
        };
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEcole(): ?string
    {
        return $this->ecole;
    }

    public function setEcole(?string $ecole): static
    {
        $this->ecole = $ecole;

        return $this;
    }

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(?string $promo): static
    {
        $this->promo = $promo;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getCguAccepteesLe(): ?\DateTimeImmutable
    {
        return $this->cguAccepteesLe;
    }

    public function accepterCgu(): static
    {
        $this->cguAccepteesLe = new \DateTimeImmutable();

        return $this;
    }

    public function getType(): TypeCompte
    {
        return $this->type;
    }

    public function setType(TypeCompte $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function estEtudiant(): bool
    {
        return TypeCompte::Etudiant === $this->type;
    }

    public function estPartenaire(): bool
    {
        return TypeCompte::Partenaire === $this->type;
    }

    public function isComptePrive(): bool
    {
        return $this->comptePrive;
    }

    public function setComptePrive(bool $comptePrive): static
    {
        $this->comptePrive = $comptePrive;

        return $this;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getNotificationsLuesLe(): ?\DateTimeImmutable
    {
        return $this->notificationsLuesLe;
    }

    public function marquerNotificationsLues(): static
    {
        $this->notificationsLuesLe = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return list<string> */
    public function getInterets(): array
    {
        return array_values($this->interets->map(static fn (InteretUtilisateur $i): string => $i->getInteret())->toArray());
    }

    /** @param list<string> $interets */
    public function setInterets(array $interets): static
    {
        $voulus = array_values(array_unique(array_filter(array_map('trim', $interets))));

        foreach ($this->interets as $existant) {
            if (!\in_array($existant->getInteret(), $voulus, true)) {
                $this->interets->removeElement($existant);
            }
        }

        $actuels = $this->getInterets();
        foreach ($voulus as $interet) {
            if (!\in_array($interet, $actuels, true)) {
                $this->interets->add(new InteretUtilisateur($this, $interet));
            }
        }

        return $this;
    }

    /** @return Collection<int, Etablissement> */
    public function getEtablissements(): Collection
    {
        return $this->etablissements;
    }

    public function getEtablissementPrincipal(): ?Etablissement
    {
        return $this->etablissements->first() ?: null;
    }

    /**
     * Vide le compte de toute donnée personnelle (RGPD) : la ligne reste pour
     * que les statistiques et les relations ne cassent pas.
     */
    public function anonymiser(): void
    {
        $this->nom = 'Compte';
        $this->prenom = 'supprimé';
        $this->email = 'supprime-'.$this->id.'@outsee.invalid';
        $this->password = '';
        $this->ecole = null;
        $this->promo = null;
        $this->ville = null;
        $this->photo = null;
        $this->dateNaissance = null;
        $this->type = TypeCompte::Supprime;
        $this->interets->clear();
    }
}
