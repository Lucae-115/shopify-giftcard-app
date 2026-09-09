<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyShopifySessionToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Missing Shopify session token.',
            ], 401);
        }

        try {
            $secret = config('services.shopify.client_secret');
            $clientId = config('services.shopify.client_id');

            $payload = JWT::decode(
                $token,
                new Key($secret, 'HS256')
            );

            // Token muss für genau unsere Shopify-App bestimmt sein.
            if (($payload->aud ?? null) !== $clientId) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Invalid token audience.',
                ], 401);
            }

            $issuerHost = parse_url($payload->iss ?? '', PHP_URL_HOST);
            $destinationHost = parse_url($payload->dest ?? '', PHP_URL_HOST);

            // iss und dest müssen zum selben Shop gehören.
            if (
                !$issuerHost ||
                !$destinationHost ||
                $issuerHost !== $destinationHost
            ) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Invalid Shopify shop.',
                ], 401);
            }

            // Validierte Shopify-Daten für nachfolgende Controller/Middleware ablegen.
            $request->attributes->set('shopify_session', $payload);

            return $next($request);

        } catch (Throwable $exception) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Invalid or expired Shopify session token.',
            ], 401);
        }
    }
}
