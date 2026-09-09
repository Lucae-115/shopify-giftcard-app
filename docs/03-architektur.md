# Architektur

Die App bleibt absichtlich klein. Die Logik steckt in wenigen Laravel-Klassen, damit der Ablauf im Bewerbungsgespraech erklaerbar bleibt.

## Ablauf

```text
Shopify Admin
-> App Bridge
-> Session Token
-> Laravel Middleware
-> GiftCardController
-> ShopifyAdminService
-> Shopify Admin GraphQL API
-> giftCardCreate
-> GiftCardDocument
-> Template Rendering
-> PDF Download
```

## Backend-Klassen

`VerifyShopifySessionToken`

Validiert das JWT aus dem Shopify Admin. Es prueft Signatur, Audience, Issuer und Destination.

`ShopifyAdminService`

Kapselt den Client-Credentials-Flow und GraphQL-Requests an Shopify.

`ShopifyFileController`

Nimmt eine Shopify File-ID entgegen und loest sie als `MediaImage` auf. Nur fertige Bilder mit Status `READY` werden akzeptiert.

`GiftCardController`

Validiert Eingaben, erstellt den Shopify-Gutschein, speichert das Dokument und liefert Vorschau/PDF-URL zurueck.

`GiftCardTemplateService`

Stellt das Standardtemplate bereit und ersetzt Platzhalter.

`PdfService`

Erzeugt aus dem gerenderten HTML ein PDF.

`GiftCardDocument`

Speichert die Daten fuer die erneute PDF-Erzeugung. Der Gutscheincode ist verschluesselt.

## Template-Platzhalter

- `{{amount}}`
- `{{currency}}`
- `{{code}}`
- `{{expires_on}}`
- `{{note}}`
- `{{qr_url}}`
- `{{qr_code}}`
- `{{image_url}}`
- `{{image_alt}}`

Platzhalterwerte werden escaped, bevor sie in die Vorlage eingesetzt werden. Die Vorschau wird nicht direkt in die App-Seite injiziert, sondern in einem sandboxed iframe angezeigt.
