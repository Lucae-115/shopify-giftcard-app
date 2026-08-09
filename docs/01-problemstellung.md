# Problemstellung

## Ausgangssituation

Bei der manuellen Erstellung von Gutscheinen in Shopify entsteht zusätzlicher Aufwand, wenn der Gutschein auch als druckbares PDF ausgegeben werden soll.

Nach dem Erstellen eines Gutscheins müssen aktuell Daten wie:

- Gutscheincode
- Gutscheinwert
- Ablaufdatum
- Motiv
- QR-Code

manuell in eine separate Gutscheinvorlage übertragen werden.

Die PDF-Datei muss anschließend ebenfalls separat erstellt werden.

## Problem

Shopify bietet für diesen individuellen Anwendungsfall keine direkte Möglichkeit, aus einem administrativ erstellten Gutschein automatisch eine frei gestaltbare PDF-Datei zu generieren.

Insbesondere bei wiederholter Erstellung von Gutscheinen führt der manuelle Prozess zu unnötigem Zeitaufwand und Fehlerpotenzial.

## Ziel des Projekts

Ziel ist die Entwicklung einer Shopify-App, die den gesamten Prozess zentralisiert.

Die Anwendung soll:

1. einen Gutschein über Shopify erstellen,
2. die dafür benötigten Gutscheindaten verarbeiten,
3. ein individuelles Motiv ermöglichen,
4. einen QR-Code erzeugen,
5. eine HTML/CSS-Vorlage mit den Gutscheindaten befüllen,
6. eine Vorschau erzeugen,
7. und das Ergebnis als PDF exportieren.

Dadurch soll aus einem bislang teilweise manuellen Prozess ein reproduzierbarer und weitgehend automatisierter Workflow entstehen.