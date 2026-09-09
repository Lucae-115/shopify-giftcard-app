<?php

namespace App\Services;

use Illuminate\Support\HtmlString;

class GiftCardTemplateService
{
    public function defaultHtml(): string
    {
        return <<<'HTML'
<main class="voucher">
    {{image_section}}

    <section class="voucher-content">
        <p class="eyebrow">Shopify Gutschein</p>
        <h1>{{amount}} {{currency}}</h1>
        <p class="subtitle">Einlösbar im Shop. Der Code ist nur für diese Karte bestimmt.</p>

        <div class="code-block">
            <span>Gutscheincode</span>
            <strong>{{code}}</strong>
        </div>

        {{expires_section}}

        {{qr_section}}
    </section>
</main>
HTML;
    }

    public function defaultCss(): string
    {
        return <<<'CSS'
@page {
    margin: 0;
}

body {
    margin: 0;
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #202223;
    background: #f6f6f7;
}

.voucher {
    width: 760px;
    min-height: 660px;
    background: #ffffff;
    border: 1px solid #d2d5d8;
}

.voucher-media {
    height: 245px;
    background: #dfe3e8;
    overflow: hidden;
}

.voucher-media img {
    width: 100%;
    height: auto;
    display: block;
}

.voucher-content {
    padding: 34px 44px 38px;
}

.eyebrow {
    margin: 0 0 8px;
    font-size: 13px;
    letter-spacing: 0;
    text-transform: uppercase;
    color: #5c5f62;
}

h1 {
    margin: 0;
    font-size: 54px;
    line-height: 1.05;
    color: #004c3f;
}

.subtitle {
    margin: 12px 0 24px;
    font-size: 16px;
    color: #5c5f62;
}

.code-block {
    border: 2px solid #004c3f;
    padding: 18px 22px;
    margin-bottom: 20px;
}

.code-block span {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    color: #5c5f62;
}

.code-block strong {
    font-family: DejaVu Sans Mono, monospace;
    font-size: 25px;
    letter-spacing: 1px;
}

.details {
    display: block;
    width: 100%;
    margin: 0 0 20px;
}

.details div {
    display: block;
}

dt {
    font-size: 12px;
    color: #5c5f62;
}

dd {
    margin: 5px 0 0;
    font-size: 15px;
}

.qr-row {
    display: table;
    width: 100%;
    margin-top: 18px;
    border: 1px solid #c9d8d3;
    background: #f3faf7;
    padding: 14px;
}

.qr-row img {
    display: table-cell;
    width: 96px;
    height: 96px;
    vertical-align: middle;
    background: #ffffff;
    border: 1px solid #d2d5d8;
}

.qr-copy {
    display: table-cell;
    padding-left: 20px;
    vertical-align: middle;
}

.qr-copy strong {
    display: block;
    margin-bottom: 6px;
    font-size: 18px;
    color: #004c3f;
}

.qr-copy span {
    display: block;
    font-size: 13px;
    color: #5c5f62;
    word-break: break-word;
}
CSS;
    }

    public function render(string $html, string $css, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            $placeholder = '{{'.$key.'}}';

            if ($value instanceof HtmlString) {
                $replacements[$placeholder] = $value->toHtml();
                continue;
            }

            $replacements[$placeholder] = e((string) ($value ?? ''));
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>'
            .$css
            .'</style></head><body>'
            .strtr($html, $replacements)
            .'</body></html>';
    }
}
