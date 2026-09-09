<?php

use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\ShopifyFileController;
use App\Http\Middleware\VerifyShopifySessionToken;
use App\Services\ShopifyAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/shopify-files/resolve', [
    ShopifyFileController::class,
    'show',
])->middleware(VerifyShopifySessionToken::class);

Route::get('/gift-card-templates/default', [
    GiftCardController::class,
    'defaults',
])->middleware(VerifyShopifySessionToken::class);

Route::post('/gift-card-templates/preview', [
    GiftCardController::class,
    'preview',
])->middleware(VerifyShopifySessionToken::class);

Route::post('/gift-cards', [
    GiftCardController::class,
    'store',
])->middleware(VerifyShopifySessionToken::class);

Route::get('/gift-card-documents/{document}/pdf', [
    GiftCardController::class,
    'pdf',
])->middleware(VerifyShopifySessionToken::class)
    ->name('gift-card-documents.pdf');
