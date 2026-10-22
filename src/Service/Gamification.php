<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Avis;
use App\Entity\Badge;
use App\Entity\BadgeObtenu;
use App\Entity\Economie;
use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\SquadMembre;
use App\Entity\SuiviUtilisateur;
use App\Entity\Utilisateur;
use App\Enum\StatutInscription;
use App\Enum\StatutSuivi;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Points d'expérience, niveaux et badges.
 *
 * XP = 15 par sortie, 10 par squad, 5 par abonnement, 8 par avis.
 * Niveau = ⌊√(XP / 50)⌋ + 1 : chaque niveau demande un peu plus que le précédent.
 */
final readonly class Gamification
{
    private const XP_SORTIE = 15;
    private const XP_SQUAD = 10;
    private const XP_ABONNEMENT = 5;
    private const XP_AVIS = 8;

    /** Inscription au moins sept jours avant la soirée. */
    private const ANTICIPATION_EARLY_BIRD = '+7 days';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /** @return array{sorties: int, squads: int, abonnements: int, avis: int, economies: float} */
    public function statistiques(Utilisateur $utilisateur): array
    {
        return [
            'sorties' => $this->compter(Inscription::class, 'utilisateur', $utilisateur, 'x.statut != :annule', ['annule' => StatutInscription::Annule]),
            'squads' => $this->compter(SquadMembre::class, 'utilisateur', $utilisateur),
            'abonnements' => $this->compter(SuiviUtilisateur::class, 'abonne', $utilisateur, 'x.statut = :accepte', ['accepte' => StatutSuivi::Accepte]),
            'avis' => $this->compter(Avis::class, 'auteur', $utilisateur),
            'economies' => (float) $this->entityManager->createQueryBuilder()
                ->select('COALESCE(SUM(x.montant), 0)')
                ->from(Economie::class, 'x')
                ->where('x.utilisateur = :u')
                ->setParameter('u', $utilisateur)
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    /** @param array{sorties: int, squads: int, abonnements: int, avis: int} $stats */
    public static function xp(array $stats): int
    {
        return $stats['sorties'] * self::XP_SORTIE
            + $stats['squads'] * self::XP_SQUAD
            + $stats['abonnements'] * self::XP_ABONNEMENT
            + $stats['avis'] * self::XP_AVIS;
    }

    public static function niveau(int $xp): int
    {
        return max(1, (int) floor(sqrt($xp / 50)) + 1);
    }

    public static function xpPourNiveau(int $niveau): int
    {
        return ($niveau - 1) ** 2 * 50;
    }

    /**
     * Débloque les badges dont la condition est remplie et renvoie ceux qui
     * viennent d'être obtenus.
     *
     * @return list<Badge>
     */
    public function attribuerBadges(Utilisateur $utilisateur, ?Evenement $evenementReserve = null): array
    {
        $stats = $this->statistiques($utilisateur);

        $conditions = [
            'first_event' => $stats['sorties'] >= 1,
            'five_events' => $stats['sorties'] >= 5,
            'ten_events' => $stats['sorties'] >= 10,
            'first_squad' => $stats['squads'] >= 1,
            'five_squads' => $stats['squads'] >= 5,
            'first_follow' => $stats['abonnements'] >= 1,
            'reviewer' => $stats['avis'] >= 1,
            'saver_50' => $stats['economies'] >= 50,
            'early_bird' => null !== $evenementReserve
                && $evenementReserve->getDateHeure() >= new \DateTimeImmutable(self::ANTICIPATION_EARLY_BIRD),
        ];

        $dejaObtenus = array_map(
            static fn (BadgeObtenu $b): string => $b->getBadge()->getCode(),
            $this->entityManager->getRepository(BadgeObtenu::class)->findBy(['utilisateur' => $utilisateur]),
        );

        $nouveaux = [];
        foreach ($conditions as $code => $remplie) {
            if (!$remplie || \in_array($code, $dejaObtenus, true)) {
                continue;
            }

            $badge = $this->entityManager->find(Badge::class, $code);
            if (null !== $badge) {
                $this->entityManager->persist(new BadgeObtenu($utilisateur, $badge));
                $nouveaux[] = $badge;
            }
        }

        $this->entityManager->flush();

        return $nouveaux;
    }

    /**
     * @param class-string         $entite
     * @param array<string, mixed> $parametres
     */
    private function compter(string $entite, string $champ, Utilisateur $utilisateur, ?string $condition = null, array $parametres = []): int
    {
        $requete = $this->entityManager->createQueryBuilder()
            ->select(sprintf('COUNT(IDENTITY(x.%s))', $champ))
            ->from($entite, 'x')
            ->where(\sprintf('x.%s = :u', $champ))
            ->setParameter('u', $utilisateur);

        if (null !== $condition) {
            $requete->andWhere($condition);
        }
        foreach ($parametres as $nom => $valeur) {
            $requete->setParameter($nom, $valeur);
        }

        return (int) $requete->getQuery()->getSingleScalarResult();
    }
}
