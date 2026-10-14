<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\EntreeJournalRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:statistiques', description: 'Résume l\'activité des derniers jours depuis le journal MongoDB')]
final readonly class StatistiquesActiviteCommand
{
    public function __construct(private EntreeJournalRepository $journal)
    {
    }

    public function __invoke(SymfonyStyle $io, #[Argument('Nombre de jours à couvrir')] int $jours = 30): int
    {
        $totaux = $this->journal->totauxParAction($jours);

        if ([] === $totaux) {
            $io->info(\sprintf('Aucune activité sur les %d derniers jours.', $jours));

            return Command::SUCCESS;
        }

        $io->title(\sprintf('Activité des %d derniers jours', $jours));
        $io->table(['Action', 'Total'], array_map(null, array_keys($totaux), array_values($totaux)));

        return Command::SUCCESS;
    }
}
