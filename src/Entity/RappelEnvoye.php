<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trace des rappels de la veille. La clé primaire rend le doublon
 * impossible : un rappel n'est jamais envoyé deux fois, même si la tâche
 * planifiée repasse.
 */
#[ORM\Entity]
#[ORM\Table(name: 'rappels_envoyes')]
class RappelEnvoye
{
    public const TYPE_VEILLE = 'veille';

    #[ORM\Column]
    private \DateTimeImmutable $envoyeLe;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Inscription $inscription,

        #[ORM\Id]
        #[ORM\Column(length: 20)]
        private string $type = self::TYPE_VEILLE,
    ) {
        $this->envoyeLe = new \DateTimeImmutable();
    }

    public function getInscription(): Inscription
    {
        return $this->inscription;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEnvoyeLe(): \DateTimeImmutable
    {
        return $this->envoyeLe;
    }
}
