<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Tests\TestCase;

class ShopifySessionTokenMiddlewareTest extends TestCase
{
    private const RETRY_HEADER = 'X-Shopify-Retry-Invalid-Session-Request';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.shopify.client_id' => 'test-client-id',
            'services.shopify.client_secret' => str_repeat('s', 32),
        ]);
    }

    public function test_missing_bearer_token_sets_shopify_retry_header(): void
    {
        $response = $this->getJson('/api/session-check');

        $response->assertUnauthorized();
        $response->assertHeader(self::RETRY_HEADER, '1');
    }

    public function test_invalid_audience_sets_shopify_retry_header(): void
    {
        $response = $this->getJson('/api/session-check', [
            'Authorization' => 'Bearer '.$this->token(['aud' => 'wrong-client-id']),
        ]);

        $response->assertUnauthorized();
        $response->assertHeader(self::RETRY_HEADER, '1');
    }

    public function test_invalid_shop_claims_set_shopify_retry_header(): void
    {
        $response = $this->getJson('/api/session-check', [
            'Authorization' => 'Bearer '.$this->token([
                'iss' => 'https://first-shop.myshopify.com',
                'dest' => 'https://second-shop.myshopify.com',
            ]),
        ]);

        $response->assertUnauthorized();
        $response->assertHeader(self::RETRY_HEADER, '1');
    }

    public function test_expired_token_sets_shopify_retry_header(): void
    {
        $response = $this->getJson('/api/session-check', [
            'Authorization' => 'Bearer '.$this->token(['exp' => time() - 60]),
        ]);

        $response->assertUnauthorized();
        $response->assertHeader(self::RETRY_HEADER, '1');
    }

    public function test_invalid_signature_sets_shopify_retry_header(): void
    {
        $token = JWT::encode($this->claims(), str_repeat('x', 32), 'HS256');

        $response = $this->getJson('/api/session-check', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertUnauthorized();
        $response->assertHeader(self::RETRY_HEADER, '1');
    }

    private function token(array $overrides = []): string
    {
        return JWT::encode(
            array_merge($this->claims(), $overrides),
            str_repeat('s', 32),
            'HS256'
        );
    }

    private function claims(): array
    {
        return [
            'aud' => 'test-client-id',
            'iss' => 'https://test-shop.myshopify.com',
            'dest' => 'https://test-shop.myshopify.com',
            'sub' => '123',
            'exp' => time() + 300,
            'nbf' => time() - 60,
            'iat' => time(),
        ];
    }
}
