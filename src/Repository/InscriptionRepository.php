<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\Utilisateur;
use App\Enum\StatutInscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Inscription> */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    /**
     * Les pass encore utiles : réservations actives dont la soirée n'est pas finie.
     *
     * @return list<Inscription>
     */
    public function passActifs(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('i')
            ->addSelect('e', 'etab')
            ->join('i.evenement', 'e')
            ->join('e.etablissement', 'etab')
            ->where('i.utilisateur = :u')
            ->andWhere('i.statut != :annule')
            ->andWhere('e.dateHeure >= :debut')
            ->setParameter('u', $utilisateur)
            ->setParameter('annule', StatutInscription::Annule)
            ->setParameter('debut', new \DateTimeImmutable('-6 hours'))
            ->orderBy('e.dateHeure', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Inscription> */
    public function historique(Utilisateur $utilisateur, int $limite = 20): array
    {
        return $this->createQueryBuilder('i')
            ->addSelect('e', 'etab')
            ->join('i.evenement', 'e')
            ->join('e.etablissement', 'etab')
            ->where('i.utilisateur = :u')
            ->andWhere('i.statut = :checkin')
            ->setParameter('u', $utilisateur)
            ->setParameter('checkin', StatutInscription::Checkin)
            ->orderBy('e.dateHeure', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Inscription> participants d'un événement, réservations annulées exclues */
    public function participants(Evenement $evenement): array
    {
        return $this->createQueryBuilder('i')
            ->addSelect('u')
            ->join('i.utilisateur', 'u')
            ->where('i.evenement = :e')
            ->andWhere('i.statut != :annule')
            ->setParameter('e', $evenement)
            ->setParameter('annule', StatutInscription::Annule)
            ->orderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
