<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Squad;
use App\Enum\TypeSquad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Squad> */
class SquadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Squad::class);
    }

    /** @return list<Squad> */
    public function aVenir(?TypeSquad $type = null, bool $associationsSeulement = false): array
    {
        $requete = $this->createQueryBuilder('s')
            ->addSelect('c', 'm')
            ->join('s.createur', 'c')
            ->leftJoin('s.membres', 'm')
            ->where('s.dateHeure >= :maintenant')
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->orderBy('s.dateHeure', 'ASC');

        if (null !== $type) {
            $requete->andWhere('s.type = :type')->setParameter('type', $type);
        }
        if ($associationsSeulement) {
            $requete->andWhere('s.creeParAssociation = true');
        }

        return $requete->getQuery()->getResult();
    }
}
