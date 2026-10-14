<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\EntreeJournal;
use App\Entity\Utilisateur;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Point d'entrée unique pour écrire dans le journal d'activité (MongoDB).
 *
 * Une panne de MongoDB ne doit jamais empêcher une réservation ou une
 * connexion : l'erreur est consignée dans les logs et l'action continue.
 */
final readonly class Journal
{
    public const CONNEXION = 'connexion';
    public const INSCRIPTION_COMPTE = 'inscription_compte';
    public const RESERVATION = 'reservation';
    public const ANNULATION = 'annulation';
    public const ENTREE_VALIDEE = 'entree_validee';
    public const SQUAD_REJOINTE = 'squad_rejointe';
    public const AVIS_DEPOSE = 'avis_depose';
    public const EVENEMENT_PUBLIE = 'evenement_publie';
    public const SUPPRESSION_COMPTE = 'suppression_compte';

    public function __construct(
        private DocumentManager $documentManager,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
    ) {
    }

    /** @param array<string, scalar|null> $details */
    public function enregistrer(string $action, ?Utilisateur $utilisateur = null, array $details = []): void
    {
        try {
            $this->documentManager->persist(new EntreeJournal($action, $utilisateur?->getId(), $details, $this->ipTronquee()));
            $this->documentManager->flush();
        } catch (\Throwable $erreur) {
            $this->logger->error('Écriture du journal d\'activité impossible', ['action' => $action, 'exception' => $erreur]);
        }
    }

    /** 192.168.1.42 devient 192.168.1.0 ; en IPv6, seul le préfixe /48 est gardé. */
    private function ipTronquee(): ?string
    {
        $ip = $this->requestStack->getCurrentRequest()?->getClientIp();
        if (null === $ip) {
            return null;
        }

        if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }

        $binaire = inet_pton($ip);

        return false === $binaire ? null : inet_ntop(substr($binaire, 0, 6).str_repeat("\0", 10));
    }
}
