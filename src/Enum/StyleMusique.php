<?php

declare(strict_types=1);

namespace App\Enum;

/** Style de musique annoncé pour une soirée. */
enum StyleMusique: string
{
    case Techno = 'techno';
    case House = 'house';
    case HipHop = 'hiphop';
    case Latino = 'latino';
    case Afro = 'afro';
    case Pop = 'pop';
    case Rock = 'rock';
    case Disco = 'disco';
    case Live = 'live';
    case Generaliste = 'generaliste';
    case Sans = 'sans';

    public function libelle(): string
    {
        return match ($this) {
            self::Techno => 'Techno',
            self::House => 'House',
            self::HipHop => 'Hip-hop / Rap',
            self::Latino => 'Latino / Reggaeton',
            self::Afro => 'Afrobeats',
            self::Pop => 'Pop / Variété',
            self::Rock => 'Rock',
            self::Disco => 'Disco / Funk',
            self::Live => 'Concert live',
            self::Generaliste => 'Généraliste',
            self::Sans => 'Sans musique',
        };
    }
}
