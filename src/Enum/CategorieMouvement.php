<?php

declare(strict_types=1);

namespace App\Enum;

/** Catégorie comptable d'un mouvement. */
enum CategorieMouvement: string
{
    case Abonnement = 'abonnement';
    case Sponsoring = 'sponsoring';
    case AutreRecette = 'autre_recette';
    case Hebergement = 'hebergement';
    case Banque = 'banque';
    case Comptabilite = 'comptabilite';
    case Assurance = 'assurance';
    case Marketing = 'marketing';
    case Juridique = 'juridique';
    case Materiel = 'materiel';
    case AutreDepense = 'autre_depense';
}
