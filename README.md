# Shopify Gift Card PDF App

Ich habe die App gebaut, weil ich für manuell ausgegebene Shopify-Gutscheine nicht jedes Mal separat eine druckbare PDF-Datei zusammenbauen wollte. Die App erstellt den Gutschein direkt in Shopify und verwendet den vollständigen Code direkt danach für ein PDF.

Der wichtige Punkt: Shopify gibt den kompletten Gutscheincode nur bei der Erstellung zurück. Deshalb wird der Code lokal verschlüsselt gespeichert, wenn das PDF später noch einmal erzeugt werden soll.

## Aktueller Stand

- Eingebettete Shopify-App mit Laravel Backend
- Session-Token-Prüfung für geschützte API-Routen
- Gutschein-Erstellung über `giftCardCreate`
- Betrag, Ablauf-Preset und interne Notiz
- Bildauswahl aus Shopify Dateien über App Bridge Intents
- Auflösung der `MediaImage`-Daten über Admin GraphQL
- QR-Code-Erzeugung serverseitig
- HTML/CSS-Template mit Platzhaltern
- Vorschau in einem sandboxed iframe
- PDF-Download per authentifiziertem `fetch()` und Blob
- lokale Speicherung der Dokumentdaten
- verschlüsselte Speicherung des vollständigen Gutscheincodes

## Screenshots

Screenshots liegen noch nicht im Repository. Geplant sind:

- App-Start im Shopify Admin
- ausgewähltes Shopify-Bild
- bearbeitetes Gutschein-Template
- fertige Vorschau
- heruntergeladenes PDF

## Architektur kurz

Der Ablauf ist bewusst direkt gehalten:

1. Shopify Admin öffnet die eingebettete App.
2. App Bridge liefert für jeden Backend-Request ein frisches ID Token.
3. Laravel validiert das JWT in `VerifyShopifySessionToken`.
4. `GiftCardController` validiert die Eingaben.
5. `ShopifyAdminService` erstellt den Gutschein per Admin GraphQL API.
6. Der vollständige Code wird verschlüsselt in `gift_card_documents` gespeichert.
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
- SQLite für lokale Entwicklung

`tecnickcom/tcpdf` wird für die PDF-Erzeugung genutzt. Die Library steht unter LGPL-3.0 und ist für diesen Einsatz als normale Composer-Dependency geeignet.

## Development Setup

```bash
cd backend
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
```

Für die lokale Entwicklung:

```bash
cd backend
composer run dev
```

Shopify CLI wird aus dem Projektroot gestartet:

```bash
npm run shopify -- app dev
```

## Shopify-Konfiguration

Benötigte Scopes:

- `read_gift_cards`
- `write_gift_cards`
- `read_files`

Die App nutzt den Client-Credentials-Flow für den Dev Store. `SHOPIFY_CLIENT_SECRET` bleibt ausschließlich im Backend.

Die Bildauswahl läuft über `shopify.intents.invoke('pick:shopify/File')`. Ausgewählt werden nur `MediaImage`-Dateien. Danach wird die File-ID serverseitig über GraphQL aufgelöst.

## Sicherheit

- `.env` wird nicht committed.
- Client Secret und Admin Access Token gehen nicht ins Frontend.
- Geschützte Routen validieren das Shopify Session Token.
- Für jeden API- und PDF-Request wird ein frischer Bearer Token geholt.
- `aud`, `iss` und `dest` werden geprüft.
- Requests werden dem Shop aus dem Session Token zugeordnet.
- Der vollständige Gutscheincode wird nicht im Klartext gespeichert.
- Die interne Notiz wird nicht im öffentlichen Gutschein gerendert.
- Technische Fehler landen im Laravel Log, nicht als Stacktrace im Frontend.
- Die Template-Vorschau läuft in einem sandboxed iframe.

## Bekannte Grenzen

- Die App erstellt neue Gutscheine. Bestehende Shopify-Gutscheine können nicht nachträglich mit komplettem Code ausgelesen werden.
- Die PDF-Erzeugung deckt einfache HTML/CSS-Templates ab. Sehr modernes CSS ist bei TCPDF nur eingeschränkt nutzbar.
- Die Shopify-Gutschein-Detailseite hat noch keine eigene Admin Extension.
- Ein echter End-to-End-Test muss im Shopify Admin geklickt werden, weil App Bridge, Session Token und File Picker dort laufen.

## Was ich technisch umgesetzt habe

- Session-Token-Validierung für eine eingebettete Shopify-App
- Admin GraphQL Mutation `giftCardCreate`
- serverseitige QR-Code-Erzeugung
- Shopify File Picker über App Bridge Intents
- Ablauf-Presets mit serverseitiger Berechnung
- HTML/CSS-Template-System mit Platzhaltern
- PDF-Erzeugung aus gerendertem HTML
- verschlüsselte Speicherung sensibler Gutscheincodes
- Tests für Template-Rendering, Validierung, Ablauf-Presets und PDF-Response
