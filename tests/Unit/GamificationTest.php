<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\Gamification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GamificationTest extends TestCase
{
    public function testLesPointsDependentDeChaqueActivite(): void
    {
        $xp = Gamification::xp(['sorties' => 2, 'squads' => 1, 'abonnements' => 3, 'avis' => 1]);

        self::assertSame(2 * 15 + 10 + 3 * 5 + 8, $xp);
    }

    /** @return iterable<string, array{int, int}> */
    public static function niveaux(): iterable
    {
        yield 'débutant' => [0, 1];
        yield 'juste avant le niveau 2' => [49, 1];
        yield 'niveau 2' => [50, 2];
        yield 'niveau 3' => [200, 3];
        yield 'niveau 5' => [800, 5];
    }

    #[DataProvider('niveaux')]
    public function testLeNiveauSuitLaRacineDesPoints(int $xp, int $niveauAttendu): void
    {
        self::assertSame($niveauAttendu, Gamification::niveau($xp));
    }

    public function testLeSeuilDUnNiveauRedonneCeNiveau(): void
    {
        foreach (range(1, 10) as $niveau) {
            self::assertSame($niveau, Gamification::niveau(Gamification::xpPourNiveau($niveau)));
        }
    }
}
