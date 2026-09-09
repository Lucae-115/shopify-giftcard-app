# Shopify Gift Card PDF App

Ich habe die App gebaut, weil ich fuer manuell ausgegebene Shopify-Gutscheine nicht jedes Mal separat eine druckbare PDF-Datei zusammenbauen wollte. Die App erstellt den Gutschein direkt in Shopify und verwendet den vollstaendigen Code direkt danach fuer ein PDF.

Der wichtige Punkt: Shopify gibt den kompletten Gutscheincode nur bei der Erstellung zurueck. Deshalb wird der Code lokal verschluesselt gespeichert, wenn das PDF spaeter noch einmal erzeugt werden soll.

## Aktueller Stand

- Eingebettete Shopify-App mit Laravel Backend
- Session-Token-Pruefung fuer geschuetzte API-Routen
- Gutschein-Erstellung ueber `giftCardCreate`
- Betrag, Ablaufdatum und interne Notiz
- Bildauswahl aus Shopify Dateien ueber App Bridge Intents
- Aufloesung der `MediaImage`-Daten ueber Admin GraphQL
- QR-Code-Erzeugung serverseitig
- HTML/CSS-Template mit Platzhaltern
- Vorschau in einem sandboxed iframe
- PDF-Download nach der Erstellung
- lokale Speicherung der Dokumentdaten
- verschluesselte Speicherung des vollstaendigen Gutscheincodes

## Screenshots

Screenshots liegen noch nicht im Repository. Geplant sind:

- App-Start im Shopify Admin
- ausgewaehltes Shopify-Bild
- bearbeitetes Gutschein-Template
- fertige Vorschau
- heruntergeladenes PDF

## Architektur kurz

Der Ablauf ist bewusst direkt gehalten:

1. Shopify Admin oeffnet die eingebettete App.
2. App Bridge liefert Session Tokens fuer Backend-Requests.
3. Laravel validiert das JWT in `VerifyShopifySessionToken`.
4. `GiftCardController` validiert die Eingaben.
5. `ShopifyAdminService` erstellt den Gutschein per Admin GraphQL API.
6. Der vollstaendige Code wird verschluesselt in `gift_card_documents` gespeichert.
7. `GiftCardTemplateService` rendert HTML/CSS mit Platzhaltern.
8. `PdfService` erstellt daraus ein PDF.

Wichtige Dateien:

- `backend/app/Http/Controllers/GiftCardController.php`
- `backend/app/Http/Controllers/ShopifyFileController.php`
- `backend/app/Services/ShopifyAdminService.php`
- `backend/app/Services/GiftCardTemplateService.php`
- `backend/app/Services/PdfService.php`
- `backend/app/Services/QrCodeService.php`
- `backend/app/Models/GiftCardDocument.php`

## Technologien

- PHP 8.4 / Laravel 13
- Shopify CLI
- Shopify App Bridge
- Shopify Admin GraphQL API
- `firebase/php-jwt`
- `endroid/qr-code`
- `tecnickcom/tcpdf`
- SQLite fuer lokale Entwicklung

`tecnickcom/tcpdf` wird fuer die PDF-Erzeugung genutzt. Die Library steht unter LGPL-3.0 und ist fuer diesen Einsatz als normale Composer-Dependency geeignet.

## Development Setup

```bash
cd backend
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
```

Fuer die lokale Entwicklung:

```bash
cd backend
composer run dev
```

Shopify CLI wird aus dem Projektroot gestartet:

```bash
npm run shopify -- app dev
```

## Shopify-Konfiguration

Benotigte Scopes:

- `read_gift_cards`
- `write_gift_cards`
- `read_files`

Die App nutzt den Client-Credentials-Flow fuer den Dev Store. `SHOPIFY_CLIENT_SECRET` bleibt ausschliesslich im Backend.

Die Bildauswahl laeuft ueber `shopify.intents.invoke('pick:shopify/File')`. Ausgewaehlt werden nur `MediaImage`-Dateien. Danach wird die File-ID serverseitig ueber GraphQL aufgeloest.

## Sicherheit

- `.env` wird nicht committed.
- Client Secret und Admin Access Token gehen nicht ins Frontend.
- Geschuetzte Routen validieren das Shopify Session Token.
- `aud`, `iss` und `dest` werden geprueft.
- Requests werden dem Shop aus dem Session Token zugeordnet.
- Der vollstaendige Gutscheincode wird nicht im Klartext gespeichert.
- Technische Fehler landen im Laravel Log, nicht als Stacktrace im Frontend.
- Die Template-Vorschau laeuft in einem sandboxed iframe.

## Bekannte Grenzen

- Die App erstellt neue Gutscheine. Bestehende Shopify-Gutscheine koennen nicht nachtraeglich mit komplettem Code ausgelesen werden.
- Die PDF-Erzeugung deckt einfache HTML/CSS-Templates ab. Sehr modernes CSS ist bei TCPDF nur eingeschraenkt nutzbar.
- Die Shopify-Gutschein-Detailseite hat noch keine eigene Admin Extension.
- Ein echter End-to-End-Test muss im Shopify Admin geklickt werden, weil App Bridge, Session Token und File Picker dort laufen.

## Was ich technisch umgesetzt habe

- Session-Token-Validierung fuer eine eingebettete Shopify-App
- Admin GraphQL Mutation `giftCardCreate`
- serverseitige QR-Code-Erzeugung
- Shopify File Picker ueber App Bridge Intents
- HTML/CSS-Template-System mit Platzhaltern
- PDF-Erzeugung aus gerendertem HTML
- verschluesselte Speicherung sensibler Gutscheincodes
- einfache Tests fuer Template-Rendering und QR-Code-Service
