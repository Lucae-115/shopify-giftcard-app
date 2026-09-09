<?php

namespace App\Http\Controllers;

use App\Models\GiftCardDocument;
use App\Services\GiftCardTemplateService;
use App\Services\PdfService;
use App\Services\QrCodeService;
use App\Services\ShopifyAdminService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'expires_preset' => ['required', Rule::in(['none', '1_year', '2_years', '3_years', 'custom'])],
            'custom_expires_on' => ['nullable', 'date', 'after:today', 'required_if:expires_preset,custom'],
            'note' => ['nullable', 'string', 'max:255'],
            'qr_url' => ['nullable', 'url'],
            'file_id' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'template_html' => ['nullable', 'string', 'max:20000'],
            'template_css' => ['nullable', 'string', 'max:20000'],
        ], $this->validationMessages());

        $expiresOn = $this->resolveExpiresOn(
            $validated['expires_preset'],
            $validated['custom_expires_on'] ?? null
        );

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

        if ($expiresOn) {
            $input['expiresOn'] = $expiresOn->toDateString();
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
                'message' => 'Shopify konnte den Gutschein nicht erstellen. Bitte später erneut versuchen.',
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
            'expires_on' => $giftCard['expiresOn'] ?? $expiresOn?->toDateString(),
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

    public function preview(
        Request $request,
        GiftCardTemplateService $templates,
        QrCodeService $qrCodeService
    ): JsonResponse {
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'code' => ['nullable', 'string', 'max:255'],
            'expires_preset' => ['required', Rule::in(['none', '1_year', '2_years', '3_years', 'custom'])],
            'custom_expires_on' => ['nullable', 'date', 'after:today', 'required_if:expires_preset,custom'],
            'note' => ['nullable', 'string', 'max:255'],
            'qr_url' => ['nullable', 'url'],
            'qr_code' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'template_html' => ['required', 'string', 'max:20000'],
            'template_css' => ['nullable', 'string', 'max:20000'],
        ], $this->validationMessages());

        $expiresOn = $this->resolveExpiresOn(
            $validated['expires_preset'],
            $validated['custom_expires_on'] ?? null
        );
        $qrCode = $validated['qr_code'] ?? null;

        if (!$qrCode && !empty($validated['qr_url'])) {
            $qrCode = $qrCodeService->generateDataUri($validated['qr_url']);
        }

        return response()->json([
            'success' => true,
            'expires_on' => $expiresOn?->toDateString(),
            'qr_code' => $qrCode,
            'preview_html' => $templates->render(
                $validated['template_html'],
                $validated['template_css'] ?? '',
                [
                    'amount' => number_format((float) ($validated['amount'] ?? 25), 2, ',', '.'),
                    'currency' => $validated['currency'] ?? 'EUR',
                    'code' => $validated['code'] ?? 'ABCD-1234-EFGH-5678',
                    'expires_on' => $expiresOn?->format('d.m.Y') ?? '',
                    'note' => '',
                    'qr_url' => $validated['qr_url'] ?? '',
                    'qr_code' => $qrCode ?? '',
                    'display_qr_url' => $this->displayUrl($validated['qr_url'] ?? null),
                    'image_url' => $validated['image_url'] ?? '',
                    'image_alt' => $validated['image_alt'] ?? 'Gutscheinmotiv',
                    'image_section' => $this->imageSection($validated['image_url'] ?? null, $validated['image_alt'] ?? null),
                    'expires_section' => $this->expiresSection($expiresOn),
                    'qr_section' => $this->qrSection($qrCode, $validated['qr_url'] ?? null),
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
                'document' => 'Dieses PDF gehört zu einem anderen Shop.',
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
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function templateData(GiftCardDocument $document): array
    {
        return [
            'amount' => number_format((float) $document->amount, 2, ',', '.'),
            'currency' => $document->currency,
            'code' => $document->gift_card_code,
            'expires_on' => $document->expires_on?->format('d.m.Y') ?? '',
            'note' => '',
            'qr_url' => $document->qr_url ?: '',
            'qr_code' => $document->qr_code ?: '',
            'display_qr_url' => $this->displayUrl($document->qr_url),
            'image_url' => $document->shopify_image_url ?: '',
            'image_alt' => $document->shopify_image_alt ?: 'Gutscheinmotiv',
            'image_section' => $this->imageSection($document->shopify_image_url, $document->shopify_image_alt),
            'expires_section' => $this->expiresSection($document->expires_on ? CarbonImmutable::parse($document->expires_on) : null),
            'qr_section' => $this->qrSection($document->qr_code, $document->qr_url),
        ];
    }

    private function resolveExpiresOn(string $preset, ?string $customDate): ?CarbonImmutable
    {
        $today = CarbonImmutable::today();

        return match ($preset) {
            '1_year' => $today->addYear(),
            '2_years' => $today->addYears(2),
            '3_years' => $today->addYears(3),
            'custom' => CarbonImmutable::parse($customDate),
            default => null,
        };
    }

    private function imageSection(?string $imageUrl, ?string $imageAlt): HtmlString
    {
        if (!$imageUrl) {
            return new HtmlString('');
        }

        return new HtmlString(sprintf(
            '<section class="voucher-media"><img src="%s" alt="%s"></section>',
            e($imageUrl),
            e($imageAlt ?: 'Gutscheinmotiv')
        ));
    }

    private function expiresSection(?CarbonImmutable $expiresOn): HtmlString
    {
        if (!$expiresOn) {
            return new HtmlString('');
        }

        return new HtmlString(sprintf(
            '<dl class="details"><div><dt>Gültig bis</dt><dd>%s</dd></div></dl>',
            e($expiresOn->format('d.m.Y'))
        ));
    }

    private function qrSection(?string $qrCode, ?string $qrUrl): HtmlString
    {
        if (!$qrCode || !$qrUrl) {
            return new HtmlString('');
        }

        return new HtmlString(sprintf(
            '<div class="qr-row"><img src="%s" alt="QR-Code"><div class="qr-copy"><strong>Gutschein online einlösen</strong><span>%s</span></div></div>',
            e($qrCode),
            e($this->displayUrl($qrUrl))
        ));
    }

    private function displayUrl(?string $url): string
    {
        if (!$url) {
            return '';
        }

        return Str::of($url)
            ->replaceStart('https://', '')
            ->replaceStart('http://', '')
            ->rtrim('/')
            ->toString();
    }

    private function validationMessages(): array
    {
        return [
            'amount.required' => 'Bitte einen gültigen Betrag größer als 0 eingeben.',
            'amount.numeric' => 'Bitte einen gültigen Betrag größer als 0 eingeben.',
            'amount.min' => 'Bitte einen gültigen Betrag größer als 0 eingeben.',
            'qr_url.url' => 'Bitte eine vollständige URL angeben, z.B. https://example.de.',
            'expires_preset.required' => 'Bitte eine Ablauf-Option auswählen.',
            'expires_preset.in' => 'Bitte eine gültige Ablauf-Option auswählen.',
            'custom_expires_on.required_if' => 'Bitte ein Ablaufdatum auswählen.',
            'custom_expires_on.date' => 'Bitte ein gültiges Ablaufdatum auswählen.',
            'custom_expires_on.after' => 'Das Ablaufdatum muss in der Zukunft liegen.',
            'image_url.url' => 'Das ausgewählte Shopify-Bild hat keine gültige URL.',
            'template_html.required' => 'Bitte ein HTML Template angeben.',
            'template_html.max' => 'Das HTML Template ist zu lang.',
            'template_css.max' => 'Das CSS ist zu lang.',
        ];
    }
}
