<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/** Adresse laissée sur la page d'attente avant l'ouverture publique. */
#[ORM\Entity]
#[ORM\Table(name: 'liste_attente')]
#[ORM\Index(name: 'idx_parrain', columns: ['parrain_id'])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse est déjà inscrite.')]
class InscritListeAttente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column]
    private \DateTimeImmutable $creeLe;

    public function __construct(
        string $email,
        #[ORM\Column(nullable: true)]
        private ?int $parrainId = null,
    ) {
        $this->email = mb_strtolower(trim($email));
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getParrainId(): ?int
    {
        return $this->parrainId;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }
}
