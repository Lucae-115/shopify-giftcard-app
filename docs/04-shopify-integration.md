# Shopify-Integration

## Ziel

Die Anwendung soll Gutscheine direkt über die Shopify Admin GraphQL API erstellen und den vollständigen Gutscheincode unmittelbar für die spätere PDF-Generierung verwenden.

## Authentifizierung

Die Anwendung wird als eingebettete Shopify-App betrieben.

Die Kommunikation zwischen Shopify Admin und Laravel erfolgt über Shopify App Bridge. Requests an geschützte Backend-Endpunkte enthalten ein kurzlebiges Shopify Session Token.

Das Laravel-Backend validiert dieses JWT unter anderem anhand von:

- Signatur
- Client-ID
- Ziel-Shop
- Aussteller
- Gültigkeitszeitraum

Für Zugriffe auf die Shopify Admin API verwendet das Backend einen serverseitigen Access Token.

## Shopify Admin API

Die Kommunikation mit Shopify wurde in einem eigenen Service gekapselt:

`App\Services\ShopifyAdminService`

Der Service übernimmt:

- Beschaffung und Zwischenspeicherung des Access Tokens
- Aufbau von GraphQL-Anfragen
- Kommunikation mit der Shopify Admin API

## Technischer Nachweis

Die Verbindung zur Shopify Admin GraphQL API wurde zunächst über eine einfache Shop-Abfrage geprüft.

Anschließend wurde ein Testgutschein über die Mutation `giftCardCreate` erstellt.

Dabei konnte erfolgreich:

- ein Gutschein in Shopify angelegt,
- ein Gutscheinwert von 10,00 EUR gesetzt,
- ein Ablaufdatum gesetzt,
- eine interne Notiz gespeichert,
- die Shopify-Gutschein-ID empfangen
- und insbesondere der vollständige Gutscheincode empfangen werden.

Damit ist bestätigt, dass der Gutscheincode unmittelbar nach der Erstellung für die spätere PDF-Generierung verwendet werden kann.

## Technische Entscheidung

Bereits existierende Shopify-Gutscheine liefern über die Admin API nicht erneut ihren vollständigen Gutscheincode.

Daher werden neue Gutscheine über die eigene Anwendung erstellt. Der dabei einmalig zurückgegebene vollständige Code wird anschließend für die Erstellung des druckbaren Gutscheins verarbeitet.

Dieser Ansatz verwendet die offizielle Shopify API und vermeidet Workarounds zum nachträglichen Auslesen bestehender Gutscheincodes.