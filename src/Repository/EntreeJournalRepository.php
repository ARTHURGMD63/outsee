<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\EntreeJournal;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use MongoDB\BSON\UTCDateTime;

/** @extends ServiceDocumentRepository<EntreeJournal> */
class EntreeJournalRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntreeJournal::class);
    }

    /** @return list<EntreeJournal> */
    public function dernieresPour(int $utilisateurId, int $limite = 20): array
    {
        return array_values($this->createQueryBuilder()
            ->field('utilisateurId')->equals($utilisateurId)
            ->sort('creeLe', 'desc')
            ->limit($limite)
            ->getQuery()
            ->toArray());
    }

    /**
     * Nombre d'actions par jour et par type, calculé par MongoDB lui-même
     * (pipeline d'agrégation) plutôt qu'en PHP.
     *
     * @return list<array{jour: string, action: string, total: int}>
     */
    public function activiteParJour(int $jours = 30): array
    {
        $depuis = new UTCDateTime(new \DateTimeImmutable(\sprintf('-%d days midnight', $jours)));

        $resultats = $this->createAggregationBuilder()
            ->match()->field('creeLe')->gte($depuis)
            ->group()
                ->field('id')->expression([
                    'jour' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$creeLe', 'timezone' => 'Europe/Paris']],
                    'action' => '$action',
                ])
                ->field('total')->sum(1)
            ->sort(['_id.jour' => 1, '_id.action' => 1])
            ->getAggregation()
            ->getIterator();

        $lignes = [];
        foreach ($resultats as $ligne) {
            $lignes[] = [
                'jour' => (string) $ligne['_id']['jour'],
                'action' => (string) $ligne['_id']['action'],
                'total' => (int) $ligne['total'],
            ];
        }

        return $lignes;
    }

    /** @return array<string, int> nombre d'entrées par action sur la période */
    public function totauxParAction(int $jours = 30): array
    {
        $totaux = [];
        foreach ($this->activiteParJour($jours) as $ligne) {
            $totaux[$ligne['action']] = ($totaux[$ligne['action']] ?? 0) + $ligne['total'];
        }
        arsort($totaux);

        return $totaux;
    }
}
