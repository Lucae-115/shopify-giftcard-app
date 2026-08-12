<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ShopifyAdminService
{
    public function getAccessToken(string $shop): string
    {
        return Cache::remember(
            "shopify_access_token_{$shop}",
            now()->addHours(23),
            function () use ($shop) {
                $response = Http::asForm()->post(
                    "https://{$shop}/admin/oauth/access_token",
                    [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('services.shopify.client_id'),
                        'client_secret' => config('services.shopify.client_secret'),
                    ]
                );

                $response->throw();

                return $response->json('access_token');
            }
        );
    }

    public function graphql(string $shop, string $query, array $variables = []): array
{
    $token = $this->getAccessToken($shop);
    $apiVersion = config('services.shopify.api_version');

    $response = Http::withHeaders([
        'X-Shopify-Access-Token' => $token,
    ])->post(
        "https://{$shop}/admin/api/{$apiVersion}/graphql.json",
        array_filter([
            'query' => $query,
            'variables' => empty($variables) ? null : $variables,
        ], fn ($value) => $value !== null)
    );

    $response->throw();

    return $response->json();
}
}