<?php

namespace Tests\Feature;

use App\Models\GiftCardDocument;
use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GiftCardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private string $shop = 'test-shop.myshopify.com';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'services.shopify.client_id' => 'test-client-id',
            'services.shopify.client_secret' => str_repeat('s', 32),
            'services.shopify.api_version' => '2026-10',
        ]);

        Cache::flush();
    }

    public function test_preview_supports_one_year_expiry_preset(): void
    {
        CarbonImmutable::setTestNow('2026-09-09');

        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'expires_preset' => '1_year',
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertJsonPath('expires_on', '2027-09-09');
        $response->assertSee('09.09.2027', false);

        CarbonImmutable::setTestNow();
    }

    public function test_preview_supports_two_year_expiry_preset(): void
    {
        CarbonImmutable::setTestNow('2026-09-09');

        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'expires_preset' => '2_years',
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertJsonPath('expires_on', '2028-09-09');

        CarbonImmutable::setTestNow();
    }

    public function test_preview_supports_three_year_expiry_preset(): void
    {
        CarbonImmutable::setTestNow('2026-09-09');

        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'expires_preset' => '3_years',
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertJsonPath('expires_on', '2029-09-09');

        CarbonImmutable::setTestNow();
    }

    public function test_preview_supports_no_expiry_date(): void
    {
        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'expires_preset' => 'none',
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertJsonPath('expires_on', null);
        $response->assertDontSee('Gültig bis', false);
    }

    public function test_preview_supports_custom_expiry_date(): void
    {
        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'expires_preset' => 'custom',
            'custom_expires_on' => now()->addDays(10)->toDateString(),
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertJsonPath('expires_on', now()->addDays(10)->toDateString());
    }

    public function test_internal_note_is_not_rendered_in_public_preview(): void
    {
        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'note' => 'Nur intern sichtbar',
        ]), $this->authHeader());

        $response->assertOk();
        $response->assertDontSee('Nur intern sichtbar', false);
    }

    public function test_image_and_qr_are_optional_in_template(): void
    {
        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'image_url' => null,
            'qr_url' => null,
            'qr_code' => null,
        ]), $this->authHeader());

        $response->assertOk();

        $html = $response->json('preview_html');

        $this->assertStringNotContainsString('<section class="voucher-media"', $html);
        $this->assertStringNotContainsString('<div class="qr-row"', $html);
    }

    public function test_validation_errors_are_returned_per_field(): void
    {
        $response = $this->postJson('/api/gift-card-templates/preview', $this->previewPayload([
            'amount' => 0,
            'qr_url' => 'keine-url',
            'expires_preset' => 'custom',
            'custom_expires_on' => null,
        ]), $this->authHeader());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'amount',
            'qr_url',
            'custom_expires_on',
        ]);
    }

    public function test_gift_card_creation_uses_expiry_preset_and_optional_image(): void
    {
        CarbonImmutable::setTestNow('2026-09-09');
        $graphqlPayloads = [];

        Http::fake(function ($request) use (&$graphqlPayloads) {
            if (str_contains($request->url(), '/oauth/access_token')) {
                return Http::response(['access_token' => 'admin-token']);
            }

            $graphqlPayloads[] = $request->data();

            return Http::response([
                'data' => [
                    'giftCardCreate' => [
                        'giftCard' => [
                            'id' => 'gid://shopify/GiftCard/1',
                            'expiresOn' => '2027-09-09',
                            'note' => 'Nur intern',
                            'balance' => [
                                'amount' => '25.00',
                                'currencyCode' => 'EUR',
                            ],
                        ],
                        'giftCardCode' => 'ABCD-1234',
                        'userErrors' => [],
                    ],
                ],
            ]);
        });

        $response = $this->postJson('/api/gift-cards', [
            'amount' => 25,
            'expires_preset' => '1_year',
            'custom_expires_on' => null,
            'note' => 'Nur intern',
            'qr_url' => null,
            'file_id' => null,
            'image_url' => null,
            'image_alt' => null,
            'template_html' => app(\App\Services\GiftCardTemplateService::class)->defaultHtml(),
            'template_css' => app(\App\Services\GiftCardTemplateService::class)->defaultCss(),
        ], $this->authHeader());

        $response->assertOk();
        $this->assertSame('2027-09-09', $graphqlPayloads[0]['variables']['input']['expiresOn']);
        $this->assertDatabaseHas('gift_card_documents', [
            'shopify_gift_card_id' => 'gid://shopify/GiftCard/1',
            'shopify_image_url' => null,
        ]);

        CarbonImmutable::setTestNow();
    }

    public function test_pdf_route_returns_downloadable_pdf_response(): void
    {
        $document = GiftCardDocument::create([
            'shop' => $this->shop,
            'shopify_gift_card_id' => 'gid://shopify/GiftCard/1',
            'gift_card_code' => 'ABCD-1234',
            'amount' => '25.00',
            'currency' => 'EUR',
            'expires_on' => null,
            'note' => 'Nur intern',
            'qr_url' => null,
            'qr_code' => null,
            'shopify_file_id' => null,
            'shopify_image_url' => null,
            'shopify_image_alt' => null,
            'template_html' => app(\App\Services\GiftCardTemplateService::class)->defaultHtml(),
            'template_css' => app(\App\Services\GiftCardTemplateService::class)->defaultCss(),
        ]);

        $response = $this->get("/api/gift-card-documents/{$document->id}/pdf", $this->authHeader());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="gift-card-'.$document->id.'.pdf"');
    }

    private function previewPayload(array $overrides = []): array
    {
        return array_merge([
            'amount' => 25,
            'currency' => 'EUR',
            'code' => 'ABCD-1234',
            'expires_preset' => 'none',
            'custom_expires_on' => null,
            'note' => null,
            'qr_url' => 'https://nordenly.test',
            'qr_code' => 'data:image/png;base64,abc',
            'image_url' => 'https://cdn.shopify.test/image.jpg',
            'image_alt' => 'Motiv',
            'template_html' => app(\App\Services\GiftCardTemplateService::class)->defaultHtml(),
            'template_css' => app(\App\Services\GiftCardTemplateService::class)->defaultCss(),
        ], $overrides);
    }

    private function authHeader(): array
    {
        $token = JWT::encode([
            'aud' => 'test-client-id',
            'iss' => "https://{$this->shop}",
            'dest' => "https://{$this->shop}",
            'sub' => '123',
            'exp' => time() + 300,
            'nbf' => time() - 60,
            'iat' => time(),
        ], str_repeat('s', 32), 'HS256');

        return ['Authorization' => "Bearer {$token}"];
    }
}
