<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Listes fermées proposées dans les formulaires. Une seule source : la liste
 * affichée et la liste qui sert à valider ne peuvent pas diverger.
 */
final class Catalogue
{
    public const INTERETS = [
        'Sorties', 'Soirées', 'Bars', 'Boîtes', 'Techno', 'Musique', 'Mixologie',
        'Running', 'Muscu', 'Vélo', 'Foot', 'Tennis', 'Yoga',
        'Cuisine', 'Voyage', 'Cinéma', 'Lecture', 'Art', 'Photo',
        'Gaming', 'Code', 'Échecs', 'Animaux', 'Bénévolat',
    ];

    public const ECOLES = ['UCA', 'SIGMA Clermont', 'INP Ingénieurs', 'IFSI', 'Autre'];

    public const PROMOS = ['L1', 'L2', 'L3', 'M1', 'M2', 'BUT1', 'BUT2', 'BUT3'];

    public const VILLES = ['Clermont-Ferrand', 'Lyon', 'Paris', 'Toulouse', 'Bordeaux', 'Autre'];

    /** Âge minimal : les établissements partenaires servent de l'alcool. */
    public const AGE_MINIMUM = 18;

    /** Au-delà, école et promo n'ont plus de sens et ne sont pas enregistrées. */
    public const AGE_MAXIMUM_ETUDIANT = 25;

    /**
     * @param list<string> $valeurs
     *
     * @return array<string, string> libellé => valeur, pour un ChoiceType
     */
    public static function choix(array $valeurs): array
    {
        return array_combine($valeurs, $valeurs);
    }
}
