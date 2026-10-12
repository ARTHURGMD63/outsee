<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Etablissement;
use App\Entity\Evenement;
use App\Enum\StatutInscription;
use App\Enum\TypeLieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Evenement> */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Événements à venir, les sponsorisés actifs en tête.
     *
     * @return list<Evenement>
     */
    public function aVenir(?TypeLieu $type = null, ?string $recherche = null, int $limite = 30): array
    {
        $maintenant = new \DateTimeImmutable();

        $qb = $this->requeteAVenir($maintenant)
            ->addSelect('CASE WHEN e.sponsorise = true AND (e.sponsorJusquAu IS NULL OR e.sponsorJusquAu > :maintenant) THEN 1 ELSE 0 END AS HIDDEN enAvant')
            ->orderBy('enAvant', 'DESC')
            ->addOrderBy('e.dateHeure', 'ASC')
            ->setMaxResults($limite);

        if (null !== $type) {
            $qb->andWhere('e.type = :type')->setParameter('type', $type);
        }

        if (null !== $recherche && '' !== trim($recherche)) {
            $qb->andWhere('e.titre LIKE :recherche OR etab.nom LIKE :recherche')
                ->setParameter('recherche', '%'.addcslashes(trim($recherche), '%_').'%');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return list<Evenement> */
    public function aVenirPour(Etablissement $etablissement): array
    {
        return $this->requeteAVenir(new \DateTimeImmutable())
            ->andWhere('e.etablissement = :etablissement')
            ->setParameter('etablissement', $etablissement)
            ->orderBy('e.dateHeure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Nombre de places prises, réservations annulées exclues. */
    public function placesPrises(Evenement $evenement): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(\App\Entity\Inscription::class, 'i')
            ->where('i.evenement = :evenement')
            ->andWhere('i.statut != :annule')
            ->setParameter('evenement', $evenement)
            ->setParameter('annule', StatutInscription::Annule)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function requeteAVenir(\DateTimeImmutable $maintenant): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->addSelect('etab')
            ->join('e.etablissement', 'etab')
            ->where('e.dateHeure >= :debut')
            ->andWhere('e.flash = false OR e.flashExpiry IS NULL OR e.flashExpiry > :maintenant')
            // Une soirée commencée reste visible tant qu'elle n'est pas finie.
            ->setParameter('debut', $maintenant->modify('-6 hours'))
            ->setParameter('maintenant', $maintenant);
    }
}
