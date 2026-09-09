<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyShopifySessionToken
{
    private const RETRY_HEADER = 'X-Shopify-Retry-Invalid-Session-Request';

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->invalidSessionResponse('Missing Shopify session token.');
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
                return $this->invalidSessionResponse('Invalid token audience.');
            }

            $issuerHost = parse_url($payload->iss ?? '', PHP_URL_HOST);
            $destinationHost = parse_url($payload->dest ?? '', PHP_URL_HOST);

            // iss und dest müssen zum selben Shop gehören.
            if (
                !$issuerHost ||
                !$destinationHost ||
                $issuerHost !== $destinationHost
            ) {
                return $this->invalidSessionResponse('Invalid Shopify shop.');
            }

            // Validierte Shopify-Daten für nachfolgende Controller/Middleware ablegen.
            $request->attributes->set('shopify_session', $payload);

            return $next($request);

        } catch (Throwable $exception) {
            return $this->invalidSessionResponse(
                'Invalid or expired Shopify session token.'
            );
        }
    }

    private function invalidSessionResponse(string $message): JsonResponse
    {
        return response()->json([
            'authenticated' => false,
            'message' => $message,
        ], 401)->header(self::RETRY_HEADER, '1');
    }
}
