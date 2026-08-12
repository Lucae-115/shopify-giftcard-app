<?php

use App\Http\Middleware\VerifyShopifySessionToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\ShopifyAdminService;

Route::get('/session-check', function (Request $request) {
    $session = $request->attributes->get('shopify_session');

    return response()->json([
        'authenticated' => true,
        'shop' => parse_url($session->dest, PHP_URL_HOST),
        'user_id' => $session->sub ?? null,
    ]);
})->middleware(VerifyShopifySessionToken::class);

Route::get('/shop-check', function (
    Request $request,
    ShopifyAdminService $shopify
) {
    $session = $request->attributes->get('shopify_session');

    $shop = parse_url($session->dest, PHP_URL_HOST);

    $result = $shopify->graphql(
        $shop,
        <<<'GRAPHQL'
        query {
            shop {
                name
                myshopifyDomain
            }
        }
        GRAPHQL
    );

    return response()->json([
        'authenticated' => true,
        'shopify' => $result['data']['shop'] ?? null,
    ]);
})->middleware(VerifyShopifySessionToken::class);