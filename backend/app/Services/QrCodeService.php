<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function generateDataUri(string $content): string
    {
        $result = new Builder(
            writer: new PngWriter(),
            data: $content,
            size: 300,
            margin: 10
        )->build();

        return $result->getDataUri();
    }
}