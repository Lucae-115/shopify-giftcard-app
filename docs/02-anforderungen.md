# Anforderungen

## Umgesetzt

- Gutschein über Shopify `giftCardCreate` erstellen
- Betrag eingeben
- Ablauf über Presets wählen: kein Ablaufdatum, 1 Jahr, 2 Jahre, 3 Jahre oder benutzerdefiniert
- interne Notiz optional setzen
- interne Notiz nicht im öffentlichen Gutschein rendern
- QR-Code-Ziel optional setzen
- Bild aus Shopify Dateien auswählen
- Shopify File-ID speichern
- Bilddaten über Admin GraphQL auflösen
- Vorschau des Bildes anzeigen
- Bild wieder entfernen
- HTML/CSS-Template bearbeiten
- Platzhalter ersetzen
- Vorschau in sandboxed iframe anzeigen
- vollständigen Gutscheincode verwenden
- Dokumentdaten lokal speichern
- Gutscheincode verschlüsselt speichern
- PDF geschützt per `fetch()` laden und als Blob herunterladen

## Bewusst nicht umgesetzt

- normaler Datei-Upload per `<input type="file">`
- Auslesen vollständiger Codes bestehender Shopify-Gutscheine
- großes WYSIWYG-System
- Migration auf React
- eigene Admin Extension für die Gutschein-Detailseite

## Nächste sinnvolle Schritte

- Historie erzeugter Dokumente anzeigen
- mehrere gespeicherte Templates verwalten
- Standardwerte pro Shop speichern
- Admin Extension prüfen, sobald ein passendes Gift-Card-Target sauber verfügbar ist
