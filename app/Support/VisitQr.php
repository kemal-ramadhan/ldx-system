<?php

namespace App\Support;

use App\Models\VisitRequest;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR code for the self check-in link of an approved visit request.
 * (bacon/bacon-qr-code is already installed through Laravel Fortify.)
 */
class VisitQr
{
    public static function checkinUrl(VisitRequest $visit): string
    {
        return route('kunjungan.checkin', ['code' => $visit->checkin_code]);
    }

    /** Inline SVG for web pages. */
    public static function svg(string $content, int $size = 220): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd()));

        return $writer->writeString($content);
    }

    /** PNG bytes for e-mail; null when GD is not available on the server. */
    public static function png(string $content, int $size = 260): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            return (new Writer(new GDLibRenderer($size, 2)))->writeString($content);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
