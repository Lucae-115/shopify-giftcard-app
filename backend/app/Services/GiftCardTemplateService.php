<?php

namespace App\Services;

class GiftCardTemplateService
{
    public function defaultHtml(): string
    {
        return <<<'HTML'
<main class="voucher">
    <section class="voucher-media">
        <img src="{{image_url}}" alt="{{image_alt}}">
    </section>

    <section class="voucher-content">
        <p class="eyebrow">Shopify Gutschein</p>
        <h1>{{amount}} {{currency}}</h1>
        <p class="subtitle">Einloesbar im Shop. Der Code ist nur fuer diese Karte bestimmt.</p>

        <div class="code-block">
            <span>Gutscheincode</span>
            <strong>{{code}}</strong>
        </div>

        <dl class="details">
            <div>
                <dt>Ablaufdatum</dt>
                <dd>{{expires_on}}</dd>
            </div>
            <div>
                <dt>Notiz</dt>
                <dd>{{note}}</dd>
            </div>
        </dl>

        <div class="qr-row">
            <img src="{{qr_code}}" alt="QR-Code">
            <p>{{qr_url}}</p>
        </div>
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
    width: 100%;
    min-height: 720px;
    background: #ffffff;
    border: 1px solid #d2d5d8;
}

.voucher-media {
    height: 255px;
    background: #dfe3e8;
    overflow: hidden;
}

.voucher-media img {
    width: 100%;
    height: 255px;
    object-fit: cover;
}

.voucher-content {
    padding: 38px 46px;
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
    margin: 12px 0 26px;
    font-size: 16px;
    color: #5c5f62;
}

.code-block {
    border: 2px solid #004c3f;
    padding: 18px 22px;
    margin-bottom: 24px;
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
    display: table;
    width: 100%;
    margin: 0 0 24px;
}

.details div {
    display: table-cell;
    width: 50%;
    padding-right: 20px;
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
    border-top: 1px solid #d2d5d8;
    padding-top: 18px;
}

.qr-row img {
    display: table-cell;
    width: 92px;
    height: 92px;
    vertical-align: middle;
}

.qr-row p {
    display: table-cell;
    padding-left: 18px;
    vertical-align: middle;
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
            $replacements['{{'.$key.'}}'] = e((string) ($value ?? ''));
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>'
            .$css
            .'</style></head><body>'
            .strtr($html, $replacements)
            .'</body></html>';
    }
}
