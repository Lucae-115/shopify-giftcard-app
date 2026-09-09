<?php

namespace Tests\Unit;

use App\Services\GiftCardTemplateService;
use PHPUnit\Framework\TestCase;

class GiftCardTemplateServiceTest extends TestCase
{
    public function test_it_replaces_known_placeholders(): void
    {
        $service = new GiftCardTemplateService();

        $html = $service->render(
            '<p>{{amount}} {{currency}} {{code}}</p>',
            'p { color: #000; }',
            [
                'amount' => '25,00',
                'currency' => 'EUR',
                'code' => 'ABCD-1234',
            ]
        );

        $this->assertStringContainsString('25,00 EUR ABCD-1234', $html);
        $this->assertStringContainsString('p { color: #000; }', $html);
    }

    public function test_it_escapes_placeholder_values(): void
    {
        $service = new GiftCardTemplateService();

        $html = $service->render(
            '<p>{{note}}</p>',
            '',
            ['note' => '<script>alert("x")</script>']
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
