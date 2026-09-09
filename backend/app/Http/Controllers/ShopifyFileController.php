<?php

namespace App\Http\Controllers;

use App\Services\ShopifyAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShopifyFileController extends Controller
{
    public function show(
        Request $request,
        ShopifyAdminService $shopify
    ): JsonResponse {
        $validated = $request->validate([
            'file_id' => ['required', 'string'],
        ]);

        $session = $request->attributes->get('shopify_session');
        $shop = parse_url($session->dest, PHP_URL_HOST);

        $query = <<<'GRAPHQL'
        query ShopifyFile($id: ID!) {
            node(id: $id) {
                ... on MediaImage {
                    id
                    alt
                    fileStatus
                    image {
                        url
                        width
                        height
                        altText
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $result = $shopify->graphql(
                $shop,
                $query,
                [
                    'id' => $validated['file_id'],
                ]
            );
        } catch (Throwable $exception) {
            Log::error('Shopify file lookup failed.', [
                'shop' => $shop,
                'file_id' => $validated['file_id'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Das Shopify-Bild konnte nicht geladen werden.',
            ], 502);
        }

        $file = $result['data']['node'] ?? null;

        if (!$file || empty($file['image']) || ($file['fileStatus'] ?? null) !== 'READY') {
            return response()->json([
                'success' => false,
                'message' => 'Die ausgewaehlte Shopify-Datei ist kein fertiges Bild.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'file' => [
                'id' => $file['id'],
                'alt' => $file['alt'],
                'status' => $file['fileStatus'],
                'url' => $file['image']['url'],
                'width' => $file['image']['width'],
                'height' => $file['image']['height'],
            ],
        ]);
    }
}
