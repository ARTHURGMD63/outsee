<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ClientCrm;
use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\MouvementFinancier;
use App\Entity\Utilisateur;
use App\Enum\SensMouvement;
use App\Enum\StatutClient;
use App\Enum\StatutInscription;
use App\Enum\StatutMouvement;
use App\Enum\TypeCompte;
use Doctrine\ORM\EntityManagerInterface;

/** Les chiffres clés du back-office, calculés en SQL par Doctrine. */
final readonly class Indicateurs
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array{etudiants: int, partenaires: int, evenements_a_venir: int, reservations_30j: int,
     *               taux_presence: ?float, mrr: float, tresorerie: float}
     */
    public function principaux(): array
    {
        $depuis = new \DateTimeImmutable('-30 days');

        $reservations = $this->scalaire(
            'SELECT COUNT(i) FROM '.Inscription::class.' i WHERE i.createdAt >= :depuis AND i.statut != :annule',
            ['depuis' => $depuis, 'annule' => StatutInscription::Annule],
        );
        $presences = $this->scalaire(
            'SELECT COUNT(i) FROM '.Inscription::class.' i WHERE i.createdAt >= :depuis AND i.statut = :checkin',
            ['depuis' => $depuis, 'checkin' => StatutInscription::Checkin],
        );

        return [
            'etudiants' => (int) $this->scalaire('SELECT COUNT(u) FROM '.Utilisateur::class.' u WHERE u.type = :t', ['t' => TypeCompte::Etudiant]),
            'partenaires' => (int) $this->scalaire('SELECT COUNT(u) FROM '.Utilisateur::class.' u WHERE u.type = :t', ['t' => TypeCompte::Partenaire]),
            'evenements_a_venir' => (int) $this->scalaire('SELECT COUNT(e) FROM '.Evenement::class.' e WHERE e.dateHeure >= :maintenant', ['maintenant' => new \DateTimeImmutable()]),
            'reservations_30j' => (int) $reservations,
            'taux_presence' => $reservations > 0 ? round($presences / $reservations * 100, 1) : null,
            'mrr' => (float) $this->scalaire('SELECT COALESCE(SUM(c.mrr), 0) FROM '.ClientCrm::class.' c WHERE c.statut = :actif', ['actif' => StatutClient::Actif]),
            'tresorerie' => $this->tresorerie(),
        ];
    }

    /** @return array<string, int> nombre de clients par étape du pipeline commercial */
    public function pipeline(): array
    {
        $lignes = $this->entityManager->createQuery('SELECT c.statut AS statut, COUNT(c) AS total FROM '.ClientCrm::class.' c GROUP BY c.statut')->getArrayResult();

        $pipeline = [];
        foreach (StatutClient::cases() as $statut) {
            $pipeline[$statut->libelle()] = 0;
        }
        foreach ($lignes as $ligne) {
            $statut = $ligne['statut'] instanceof StatutClient ? $ligne['statut'] : StatutClient::from($ligne['statut']);
            $pipeline[$statut->libelle()] = (int) $ligne['total'];
        }

        return $pipeline;
    }

    /** Solde réel : seuls les mouvements réglés comptent, l'engagé n'est pas encore en banque. */
    private function tresorerie(): float
    {
        $requete = 'SELECT COALESCE(SUM(m.montantHt * (1 + m.tvaTaux / 100)), 0) FROM '.MouvementFinancier::class.' m WHERE m.sens = :sens AND m.statut = :regle';

        $recettes = (float) $this->scalaire($requete, ['sens' => SensMouvement::Recette, 'regle' => StatutMouvement::Regle]);
        $depenses = (float) $this->scalaire($requete, ['sens' => SensMouvement::Depense, 'regle' => StatutMouvement::Regle]);

        return round($recettes - $depenses, 2);
    }

    /** @param array<string, mixed> $parametres */
    private function scalaire(string $dql, array $parametres): float|int|string
    {
        $requete = $this->entityManager->createQuery($dql);
        foreach ($parametres as $nom => $valeur) {
            $requete->setParameter($nom, $valeur);
        }

        return $requete->getSingleScalarResult();
    }
}
