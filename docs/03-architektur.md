# Architektur

## Überblick

Die Anwendung wird als Shopify-App umgesetzt.

Sie besteht aus mehreren klar getrennten Komponenten:

1. Shopify-Integration
2. PHP-Backend
3. Gutschein-Template
4. QR-Code-Generierung
5. PDF-Generierung
6. Datenspeicherung

## Geplanter Ablauf

Der Benutzer gibt die benötigten Gutscheindaten innerhalb der Anwendung ein.

Anschließend wird über die Shopify Admin API ein neuer Gutschein erstellt.

Shopify gibt bei der Erstellung einmalig den vollständigen Gutscheincode zurück. Dieser wird unmittelbar für die Generierung des PDF-Gutscheins verwendet.

Der vereinfachte Ablauf lautet:

Benutzereingabe
→ PHP-Backend
→ Shopify Admin API
→ Gutschein wird erstellt
→ vollständiger Gutscheincode wird zurückgegeben
→ HTML/CSS-Vorlage wird befüllt
→ QR-Code wird erzeugt
→ Vorschau wird erstellt
→ PDF wird generiert

## Shopify-Integration

Die Kommunikation mit Shopify erfolgt über die Shopify Admin GraphQL API.

Die Anwendung soll insbesondere folgende Aufgaben übernehmen:

- Gutschein erstellen
- Gutscheinwert übergeben
- Ablaufdatum übergeben
- Gutscheincode bei der Erstellung verarbeiten
- Shopify-Gutschein-ID speichern

Später soll zusätzlich eine Integration direkt in die Gutscheinverwaltung von Shopify erfolgen.

## Backend

Die zentrale Geschäftslogik wird in PHP umgesetzt.

Das Backend übernimmt unter anderem:

- Validierung der Benutzereingaben
- Kommunikation mit Shopify
- Verarbeitung der Gutscheindaten
- Verwaltung von Vorlagen
- Verarbeitung hochgeladener Bilder
- QR-Code-Generierung
- PDF-Generierung

## Template-System

Der Gutschein basiert auf einer HTML/CSS-Vorlage.

Platzhalter innerhalb der Vorlage werden durch konkrete Gutscheindaten ersetzt.

Beispiele:

- Gutscheinwert
- Gutscheincode
- Ablaufdatum
- Motiv
- QR-Code

Dadurch kann die Gestaltung unabhängig von der eigentlichen Geschäftslogik angepasst werden.

## Datenspeicherung

Für bereits erstellte Gutscheine sollen relevante Daten lokal gespeichert werden.

Dazu gehören beispielsweise:

- Shopify-Gutschein-ID
- Gutscheincode
- verwendete Vorlage
- verwendetes Motiv
- QR-Code-Ziel
- Erstellungsdatum

Die genaue Datenbankstruktur wird in einem späteren Schritt festgelegt.

## Erweiterbarkeit

Die Architektur soll so aufgebaut werden, dass spätere Funktionen ergänzt werden können, ohne die bestehende Gutscheinlogik grundlegend verändern zu müssen.

Mögliche Erweiterungen sind:

- mehrere Templates
- Template-Editor
- Shopify Admin Extension
- erneute PDF-Generierung
- Verwaltung bestehender Gutscheine