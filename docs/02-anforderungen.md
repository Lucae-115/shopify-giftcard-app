# Anforderungen

## Umgesetzt

- Gutschein ueber Shopify `giftCardCreate` erstellen
- Betrag eingeben
- Ablaufdatum optional setzen
- interne Notiz optional setzen
- QR-Code-Ziel optional setzen
- Bild aus Shopify Dateien auswaehlen
- Shopify File-ID speichern
- Bilddaten ueber Admin GraphQL aufloesen
- Vorschau des Bildes anzeigen
- HTML/CSS-Template bearbeiten
- Platzhalter ersetzen
- Vorschau in sandboxed iframe anzeigen
- vollstaendigen Gutscheincode verwenden
- Dokumentdaten lokal speichern
- Gutscheincode verschluesselt speichern
- PDF erzeugen und herunterladen

## Bewusst nicht umgesetzt

- normaler Datei-Upload per `<input type="file">`
- Auslesen vollstaendiger Codes bestehender Shopify-Gutscheine
- grosses WYSIWYG-System
- Migration auf React
- eigene Admin Extension fuer die Gutschein-Detailseite

## Naechste sinnvolle Schritte

- Historie erzeugter Dokumente anzeigen
- mehrere gespeicherte Templates verwalten
- Standardwerte pro Shop speichern
- Admin Extension pruefen, sobald ein passendes Gift-Card-Target sauber verfuegbar ist
