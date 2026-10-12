<?php

declare(strict_types=1);

namespace App\Enum;

/** Rôle d'un compte. « Supprime » : compte anonymisé, gardé pour les statistiques. */
enum TypeCompte: string
{
    case Etudiant = 'etudiant';
    case Partenaire = 'partenaire';
    case Admin = 'admin';
    case Supprime = 'supprime';
}
