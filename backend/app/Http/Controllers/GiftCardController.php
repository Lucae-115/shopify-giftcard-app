<?php

namespace App\Http\Controllers;

use App\Models\GiftCardDocument;
use App\Services\GiftCardTemplateService;
use App\Services\PdfService;
use App\Services\QrCodeService;
use App\Services\ShopifyAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GiftCardController extends Controller
{
    public function store(
        Request $request,
        ShopifyAdminService $shopify,
        QrCodeService $qrCodeService,
        GiftCardTemplateService $templates
    ): JsonResponse {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'qr_url' => ['nullable', 'url'],
            'file_id' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'template_html' => ['nullable', 'string', 'max:20000'],
            'template_css' => ['nullable', 'string', 'max:20000'],
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
                'amount' => number_format(
                    (float) $validated['amount'],
                    2,
                    '.',
                    ''
                ),
                'currencyCode' => 'EUR',
            ],
        ];

        if (!empty($validated['expires_on'])) {
            $input['expiresOn'] = $validated['expires_on'];
        }

        if (!empty($validated['note'])) {
            $input['note'] = $validated['note'];
        }

        try {
            $result = $shopify->graphql(
                $shop,
                $mutation,
                ['input' => $input]
            );
        } catch (Throwable $exception) {
            Log::error('Shopify gift card creation failed.', [
                'shop' => $shop,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Shopify konnte den Gutschein nicht erstellen. Bitte spaeter erneut versuchen.',
            ], 502);
        }

        $payload = $result['data']['giftCardCreate'] ?? null;

        if (!$payload) {
            Log::warning('Unexpected Shopify gift card response.', [
                'shop' => $shop,
                'response' => $result,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Shopify hat eine unerwartete Antwort geliefert.',
            ], 502);
        }

        if (!empty($payload['userErrors'])) {
            return response()->json([
                'success' => false,
                'errors' => $payload['userErrors'],
            ], 422);
        }

        $qrCode = null;

        if (!empty($validated['qr_url'])) {
            $qrCode = $qrCodeService->generateDataUri(
                $validated['qr_url']
            );
        }

        $giftCard = $payload['giftCard'];
        $document = GiftCardDocument::create([
            'shop' => $shop,
            'shopify_gift_card_id' => $giftCard['id'],
            'gift_card_code' => $payload['giftCardCode'],
            'amount' => $giftCard['balance']['amount'] ?? $validated['amount'],
            'currency' => $giftCard['balance']['currencyCode'] ?? 'EUR',
            'expires_on' => $giftCard['expiresOn'] ?? ($validated['expires_on'] ?? null),
            'note' => $validated['note'] ?? null,
            'qr_url' => $validated['qr_url'] ?? null,
            'qr_code' => $qrCode,
            'shopify_file_id' => $validated['file_id'] ?? null,
            'shopify_image_url' => $validated['image_url'] ?? null,
            'shopify_image_alt' => $validated['image_alt'] ?? null,
            'template_html' => $validated['template_html'] ?? $templates->defaultHtml(),
            'template_css' => $validated['template_css'] ?? $templates->defaultCss(),
        ]);

        $previewHtml = $templates->render(
            $document->template_html,
            $document->template_css,
            $this->templateData($document)
        );

        return response()->json([
            'success' => true,
            'document_id' => $document->id,
            'gift_card' => $giftCard,
            'code' => $document->gift_card_code,
            'qr_url' => $document->qr_url,
            'qr_code' => $qrCode,
            'image_url' => $document->shopify_image_url,
            'preview_html' => $previewHtml,
            'pdf_url' => route('gift-card-documents.pdf', ['document' => $document->id], false),
        ]);
    }

    public function defaults(GiftCardTemplateService $templates): JsonResponse
    {
        return response()->json([
            'html' => $templates->defaultHtml(),
            'css' => $templates->defaultCss(),
        ]);
    }

    public function preview(Request $request, GiftCardTemplateService $templates): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'code' => ['nullable', 'string', 'max:255'],
            'expires_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'qr_url' => ['nullable', 'url'],
            'qr_code' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'template_html' => ['required', 'string', 'max:20000'],
            'template_css' => ['nullable', 'string', 'max:20000'],
        ]);

        return response()->json([
            'success' => true,
            'preview_html' => $templates->render(
                $validated['template_html'],
                $validated['template_css'] ?? '',
                [
                    'amount' => number_format((float) ($validated['amount'] ?? 25), 2, ',', '.'),
                    'currency' => $validated['currency'] ?? 'EUR',
                    'code' => $validated['code'] ?? 'ABCD-1234-EFGH-5678',
                    'expires_on' => $validated['expires_on'] ?? 'Kein Ablaufdatum',
                    'note' => $validated['note'] ?? 'Keine interne Notiz',
                    'qr_url' => $validated['qr_url'] ?? '',
                    'qr_code' => $validated['qr_code'] ?? '',
                    'image_url' => $validated['image_url'] ?? '',
                    'image_alt' => $validated['image_alt'] ?? 'Gutscheinmotiv',
                ]
            ),
        ]);
    }

    public function pdf(
        Request $request,
        GiftCardDocument $document,
        GiftCardTemplateService $templates,
        PdfService $pdf
    ): Response {
        $session = $request->attributes->get('shopify_session');
        $shop = parse_url($session->dest, PHP_URL_HOST);

        if ($document->shop !== $shop) {
            throw ValidationException::withMessages([
                'document' => 'Dieses PDF gehoert zu einem anderen Shop.',
            ]);
        }

        $html = $templates->render(
            $document->template_html,
            $document->template_css,
            $this->templateData($document)
        );

        try {
            $contents = $pdf->render($html);
        } catch (Throwable $exception) {
            Log::error('Gift card PDF generation failed.', [
                'document_id' => $document->id,
                'shop' => $shop,
                'message' => $exception->getMessage(),
            ]);

            return response('PDF konnte nicht erzeugt werden.', 500);
        }

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="gift-card-'.$document->id.'.pdf"',
        ]);
    }

    private function templateData(GiftCardDocument $document): array
    {
        return [
            'amount' => number_format((float) $document->amount, 2, ',', '.'),
            'currency' => $document->currency,
            'code' => $document->gift_card_code,
            'expires_on' => $document->expires_on?->format('d.m.Y') ?? 'Kein Ablaufdatum',
            'note' => $document->note ?: 'Keine interne Notiz',
            'qr_url' => $document->qr_url ?: '',
            'qr_code' => $document->qr_code ?: '',
            'image_url' => $document->shopify_image_url ?: '',
            'image_alt' => $document->shopify_image_alt ?: 'Gutscheinmotiv',
        ];
    }
}
