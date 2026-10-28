<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EntreeJournalRepository;
use App\Service\Indicateurs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Back-office : les chiffres métier viennent de MariaDB, l'activité de
 * MongoDB. Les deux bases se complètent sur le même écran.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'admin_tableau_de_bord', methods: ['GET'])]
    public function tableauDeBord(Indicateurs $indicateurs, EntreeJournalRepository $journal): Response
    {
        return $this->render('admin/tableau_de_bord.html.twig', [
            'indicateurs' => $indicateurs->principaux(),
            'pipeline' => $indicateurs->pipeline(),
            'activite' => $journal->totauxParAction(30),
            'activite_par_jour' => $journal->activiteParJour(14),
        ]);
    }
}
