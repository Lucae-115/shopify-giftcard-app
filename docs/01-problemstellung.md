# Problemstellung

Ich wollte einen einfachen Ablauf für individuelle Shopify-Gutscheine bauen.

Ohne App muss ich den Gutschein in Shopify erstellen, den Code kopieren, ein Motiv aussuchen, einen QR-Code erzeugen und alles in einer separaten PDF-Vorlage zusammensetzen. Das ist für einzelne Gutscheine machbar, wird aber schnell fehleranfällig.

Das eigentliche Problem ist der Gutscheincode. Shopify gibt den vollständigen Code über die Admin API nur bei der Erstellung zurück. Später ist er nicht mehr vollständig abrufbar.

Die App löst deshalb genau diesen Ablauf:

1. Gutschein in Shopify erstellen.
2. Vollständigen Code direkt verarbeiten.
3. Bild aus Shopify Dateien verwenden.
4. QR-Code erzeugen.
5. HTML/CSS-Vorlage rendern.
6. PDF herunterladen.

Der Fokus liegt nicht auf einer großen Gutscheinplattform, sondern auf einem nachvollziehbaren Workflow für individuell gestaltete PDF-Gutscheine.
