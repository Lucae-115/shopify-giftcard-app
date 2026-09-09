# Architektur

Die App bleibt absichtlich klein. Die Logik steckt in wenigen Laravel-Klassen, damit der Ablauf im Bewerbungsgespräch erklärbar bleibt.

## Ablauf

```text
Shopify Admin
-> App Bridge
-> frisches ID Token pro Request
-> Laravel Middleware
-> GiftCardController
-> ShopifyAdminService
-> Shopify Admin GraphQL API
-> giftCardCreate
-> GiftCardDocument
-> Template Rendering
-> authentifizierter PDF Download
```

## Backend-Klassen

`VerifyShopifySessionToken`

Validiert das JWT aus dem Shopify Admin. Es prüft Signatur, Audience, Issuer und Destination.

`ShopifyAdminService`

Kapselt den Client-Credentials-Flow und GraphQL-Requests an Shopify.

`ShopifyFileController`

Nimmt eine Shopify File-ID entgegen und löst sie als `MediaImage` auf. Nur fertige Bilder mit Status `READY` werden akzeptiert.

`GiftCardController`

Validiert Eingaben, berechnet Ablauf-Presets serverseitig, erstellt den Shopify-Gutschein, speichert das Dokument und liefert Vorschau/PDF-URL zurück.

`GiftCardTemplateService`

Stellt das Standardtemplate bereit und ersetzt Platzhalter. Serverseitig erzeugte Abschnitte wie Bild, Ablaufdatum und QR-Footer werden nur gerendert, wenn Daten vorhanden sind.

`PdfService`

Erzeugt aus dem gerenderten HTML ein PDF.

`GiftCardDocument`

Speichert die Daten für die erneute PDF-Erzeugung. Der Gutscheincode ist verschlüsselt.

## Template-Platzhalter

- `{{amount}}`
- `{{currency}}`
- `{{code}}`
- `{{expires_on}}`
- `{{qr_url}}`
- `{{display_qr_url}}`
- `{{qr_code}}`
- `{{image_url}}`
- `{{image_alt}}`
- `{{image_section}}`
- `{{expires_section}}`
- `{{qr_section}}`

Die interne Notiz ist kein öffentlicher Platzhalter. Sie wird an Shopify gesendet und lokal gespeichert, aber nicht im Gutschein gerendert.

Platzhalterwerte werden escaped, bevor sie in die Vorlage eingesetzt werden. Die Vorschau wird nicht direkt in die App-Seite injiziert, sondern in einem sandboxed iframe angezeigt.
