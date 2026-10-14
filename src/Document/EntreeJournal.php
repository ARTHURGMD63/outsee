<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\EntreeJournalRepository;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

/**
 * Une action faite dans l'application (connexion, réservation, scan…).
 *
 * Le journal vit dans MongoDB et non dans MariaDB : il ne fait que grossir,
 * on n'y fait jamais de jointure, et chaque action porte des détails
 * différents. Un document par action, avec un champ « details » libre,
 * convient mieux qu'une table aux colonnes figées.
 *
 * Les entrées s'effacent seules au bout d'un an (index TTL) : on ne garde
 * pas de trace d'activité plus longtemps que nécessaire.
 */
#[ODM\Document(collection: 'journal_activite', repositoryClass: EntreeJournalRepository::class)]
#[ODM\Index(keys: ['utilisateurId' => 'asc', 'creeLe' => 'desc'])]
#[ODM\Index(keys: ['action' => 'asc', 'creeLe' => 'desc'])]
#[ODM\Index(keys: ['creeLe' => 'asc'], expireAfterSeconds: self::DUREE_CONSERVATION)]
class EntreeJournal
{
    public const DUREE_CONSERVATION = 365 * 24 * 3600;

    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'date_immutable')]
    private \DateTimeImmutable $creeLe;

    /**
     * @param array<string, scalar|null> $details
     */
    public function __construct(
        #[ODM\Field(type: 'string')]
        private string $action,

        #[ODM\Field(type: 'int', nullable: true)]
        private ?int $utilisateurId = null,

        #[ODM\Field(type: 'hash')]
        private array $details = [],

        /** Adresse IP tronquée : assez pour repérer un abus, pas pour identifier quelqu'un. */
        #[ODM\Field(type: 'string', nullable: true)]
        private ?string $ip = null,
    ) {
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    /** @return array<string, scalar|null> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }
}
