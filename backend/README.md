# Backend

Das Backend ist eine Laravel-App. Es uebernimmt die Arbeit, die nicht ins Frontend gehoert: Shopify API, Session-Token-Pruefung, QR-Code, Template-Rendering, PDF-Erzeugung und Speicherung der Dokumentdaten.

## Start

```bash
composer install
npm install
php artisan migrate
composer run dev
```

## Wichtige Umgebungsvariablen

```env
APP_KEY=
SHOPIFY_CLIENT_ID=
SHOPIFY_CLIENT_SECRET=
SHOPIFY_API_VERSION=2026-10
```

`SHOPIFY_CLIENT_SECRET` darf nicht ins Frontend und nicht ins Repository.

## API-Routen

- `GET /api/session-check`
- `GET /api/shop-check`
- `POST /api/shopify-files/resolve`
- `GET /api/gift-card-templates/default`
- `POST /api/gift-card-templates/preview`
- `POST /api/gift-cards`
- `GET /api/gift-card-documents/{document}/pdf`

Alle API-Routen laufen durch `VerifyShopifySessionToken`.

## Datenbank

Die Tabelle `gift_card_documents` speichert die Daten, die fuer ein PDF gebraucht werden. Der vollstaendige Gutscheincode wird ueber Laravels `encrypted` Cast gespeichert.

Keine Access Tokens und keine Client Secrets werden in dieser Tabelle abgelegt.

## Tests und Checks

```bash
composer validate
php artisan route:list
php artisan test
npm run build
```
