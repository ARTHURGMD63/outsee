<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TypeInteraction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Échange avec un client. La prochaine action vit dans la même ligne que
 * l'échange qui l'a produite : séparée, elle se perd.
 */
#[ORM\Entity]
#[ORM\Table(name: 'crm_interactions')]
#[ORM\Index(name: 'k_client', columns: ['client_id', 'created_at'])]
#[ORM\Index(name: 'k_relance', columns: ['prochaine_action_le', 'fait'])]
class InteractionCrm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $prochaineAction = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $prochaineActionLe = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $fait = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'interactions')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ClientCrm $client,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Utilisateur $auteur,

        #[ORM\Column(length: 20, enumType: TypeInteraction::class, options: ['default' => 'note'])]
        private TypeInteraction $type,

        #[ORM\Column(type: Types::TEXT)]
        #[Assert\NotBlank]
        private string $contenu,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ClientCrm
    {
        return $this->client;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function getType(): TypeInteraction
    {
        return $this->type;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function planifier(string $action, \DateTimeImmutable $le): static
    {
        $this->prochaineAction = $action;
        $this->prochaineActionLe = $le;
        $this->fait = false;

        return $this;
    }

    public function getProchaineAction(): ?string
    {
        return $this->prochaineAction;
    }

    public function getProchaineActionLe(): ?\DateTimeImmutable
    {
        return $this->prochaineActionLe;
    }

    public function isFait(): bool
    {
        return $this->fait;
    }

    public function marquerFait(): void
    {
        $this->fait = true;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
