<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lien de réinitialisation du mot de passe. Seule l'empreinte SHA-256 du
 * jeton est gardée : une fuite de la table ne donne accès à aucun compte.
 * Le lien expire au bout d'une heure et ne sert qu'une fois.
 */
#[ORM\Entity]
#[ORM\Table(name: 'password_resets')]
#[ORM\Index(name: 'idx_email', columns: ['email'])]
class DemandeReinitialisation
{
    public const DUREE_DE_VIE = '+1 hour';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $tokenHash;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(options: ['default' => false])]
    private bool $used = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column]
        private string $email,
        string $jeton,
    ) {
        $this->tokenHash = self::empreinte($jeton);
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $this->createdAt->modify(self::DUREE_DE_VIE);
    }

    public static function empreinte(string $jeton): string
    {
        return hash('sha256', $jeton);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function estValide(\DateTimeImmutable $maintenant = new \DateTimeImmutable()): bool
    {
        return !$this->used && $this->expiresAt > $maintenant;
    }

    public function consommer(): void
    {
        $this->used = true;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
