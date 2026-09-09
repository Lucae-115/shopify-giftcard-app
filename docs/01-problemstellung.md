# Problemstellung

Ich wollte einen einfachen Ablauf fuer individuelle Shopify-Gutscheine bauen.

Ohne App muss ich den Gutschein in Shopify erstellen, den Code kopieren, ein Motiv aussuchen, einen QR-Code erzeugen und alles in einer separaten PDF-Vorlage zusammensetzen. Das ist fuer einzelne Gutscheine machbar, wird aber schnell fehleranfaellig.

Das eigentliche Problem ist der Gutscheincode. Shopify gibt den vollstaendigen Code ueber die Admin API nur bei der Erstellung zurueck. Spaeter ist er nicht mehr vollstaendig abrufbar.

Die App loest deshalb genau diesen Ablauf:

1. Gutschein in Shopify erstellen.
2. Vollstaendigen Code direkt verarbeiten.
3. Bild aus Shopify Dateien verwenden.
4. QR-Code erzeugen.
5. HTML/CSS-Vorlage rendern.
6. PDF herunterladen.

Der Fokus liegt nicht auf einer grossen Gutscheinplattform, sondern auf einem nachvollziehbaren Workflow fuer individuell gestaltete PDF-Gutscheine.
