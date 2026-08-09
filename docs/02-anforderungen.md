# Anforderungen

## Muss-Anforderungen

Die erste funktionsfähige Version der Anwendung soll folgende Funktionen enthalten:

- Erstellung eines Gutscheins über Shopify
- Verarbeitung des vollständigen Gutscheincodes bei der Erstellung
- Eingabe eines Gutscheinwerts
- Eingabe eines Ablaufdatums
- Auswahl eines vorhandenen Gutscheinmotivs
- Upload eines eigenen Gutscheinmotivs
- Eingabe einer Ziel-URL für einen QR-Code
- Generierung eines QR-Codes
- Befüllen einer HTML/CSS-Vorlage mit den Gutscheindaten
- Vorschau des fertigen Gutscheins
- Export des Gutscheins als PDF

## Soll-Anforderungen

Nach Fertigstellung des MVP sollen folgende Funktionen ergänzt werden:

- Mehrere Gutscheinvorlagen
- Erneute Generierung bereits erstellter Gutscheine
- Speicherung der Zuordnung zwischen Shopify-Gutschein und PDF-Daten
- Integration in die Shopify-Gutscheinverwaltung
- Validierung der Benutzereingaben
- Fehlerbehandlung für fehlgeschlagene Shopify-Anfragen

## Kann-Anforderungen

Optionale Erweiterungen:

- HTML/CSS-Editor für eigene Vorlagen
- Verwaltung einer Motiv-Galerie
- Vorschau verschiedener Templates
- Historie erzeugter PDFs
- Unterstützung bereits vorhandener Shopify-Gutscheine
- Konfigurierbare Standardwerte für QR-Code und Ablaufdatum

## Abgrenzung

Die erste Version ist keine allgemeine Gutscheinplattform.

Der Fokus liegt auf einem klar definierten Workflow:

Shopify-Gutschein erstellen → gestalten → Vorschau erzeugen → PDF exportieren.