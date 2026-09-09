<?php

namespace Tests\Unit;

use App\Services\QrCodeService;
use PHPUnit\Framework\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function test_it_generates_png_data_uri(): void
    {
        $service = new QrCodeService();

        $dataUri = $service->generateDataUri('https://example.test');

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }
}
