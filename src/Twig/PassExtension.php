<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Inscription;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Twig\Attribute\AsTwigFunction;

/** QR code du pass, dessiné côté serveur en SVG : aucun script à charger. */
final class PassExtension
{
    /** Préfixe lu par le scanner partenaire. */
    public const PREFIXE = 'outsee:';

    #[AsTwigFunction('qr_pass', isSafe: ['html'])]
    public function qrPass(Inscription $inscription, int $taille = 240): string
    {
        $resultat = (new Builder(
            writer: new SvgWriter(),
            writerOptions: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
            data: self::PREFIXE.$inscription->getQrCode(),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $taille,
            margin: 8,
        ))->build();

        return $resultat->getString();
    }
}
