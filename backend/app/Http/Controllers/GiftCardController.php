<?php

namespace App\Http\Controllers;

use App\Services\ShopifyAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function store(
        Request $request,
        ShopifyAdminService $shopify
    ): JsonResponse {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expires_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $session = $request->attributes->get('shopify_session');
        $shop = parse_url($session->dest, PHP_URL_HOST);

        $mutation = <<<'GRAPHQL'
        mutation GiftCardCreate($input: GiftCardCreateInput!) {
            giftCardCreate(input: $input) {
                giftCard {
                    id
                    expiresOn
                    note
                    balance {
                        amount
                        currencyCode
                    }
                }
                giftCardCode
                userErrors {
                    field
                    message
                    code
                }
            }
        }
        GRAPHQL;

        $input = [
            'initialAmount' => [
                'amount' => number_format((float) $validated['amount'], 2, '.', ''),
                'currencyCode' => 'EUR',
            ],
        ];

        if (!empty($validated['expires_on'])) {
            $input['expiresOn'] = $validated['expires_on'];
        }

        if (!empty($validated['note'])) {
            $input['note'] = $validated['note'];
        }

        $result = $shopify->graphql(
            $shop,
            $mutation,
            ['input' => $input]
        );

        $payload = $result['data']['giftCardCreate'] ?? null;

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Shopify returned an unexpected response.',
                'shopify' => $result,
            ], 502);
        }

        if (!empty($payload['userErrors'])) {
            return response()->json([
                'success' => false,
                'errors' => $payload['userErrors'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'gift_card' => $payload['giftCard'],
            'code' => $payload['giftCardCode'],
        ]);
    }
}