<?php

namespace App\Helpers;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeHelper
{
    public static function svg(string $code, int $size = 320): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($code);
    }

    public static function png(string $code, int $size = 320): string
    {
        $matrix = Encoder::encode($code, ErrorCorrectionLevel::H())->getMatrix();
        $modules = $matrix->getWidth();
        $margin = 2;
        $scale = max(1, intdiv($size, $modules + 2 * $margin));
        $side = ($modules + 2 * $margin) * $scale;

        $image = imagecreatetruecolor($side, $side);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, $side, $side, $white);

        for ($row = 0; $row < $modules; $row++) {
            for ($column = 0; $column < $modules; $column++) {
                if ($matrix->get($column, $row)) {
                    imagefilledrectangle(
                        $image,
                        ($column + $margin) * $scale,
                        ($row + $margin) * $scale,
                        ($column + $margin + 1) * $scale - 1,
                        ($row + $margin + 1) * $scale - 1,
                        $black
                    );
                }
            }
        }

        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
