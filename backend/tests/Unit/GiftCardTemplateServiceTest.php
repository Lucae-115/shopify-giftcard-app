<?php

namespace Tests\Unit;

use App\Services\GiftCardTemplateService;
use Illuminate\Support\HtmlString;
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

    public function test_it_keeps_server_generated_html_sections_raw(): void
    {
        $service = new GiftCardTemplateService();

        $html = $service->render(
            '<main>{{image_section}}</main>',
            '',
            ['image_section' => new HtmlString('<section>Bild</section>')]
        );

        $this->assertStringContainsString('<section>Bild</section>', $html);
    }

    public function test_default_template_does_not_show_internal_note(): void
    {
        $service = new GiftCardTemplateService();

        $this->assertStringNotContainsString('{{note}}', $service->defaultHtml());
        $this->assertStringNotContainsString('Notiz', $service->defaultHtml());
    }
}
