# Shopify-Integration

## Authentifizierung

Die App läuft eingebettet im Shopify Admin. Frontend-Requests an `/api/*` enthalten ein Shopify ID Token als Bearer Token. Laravel validiert dieses Token in `VerifyShopifySessionToken`.

Geprüft wird:

- Signatur mit `SHOPIFY_CLIENT_SECRET`
- `aud` gegen `SHOPIFY_CLIENT_ID`
- `iss` und `dest`
- gleicher Shop in Issuer und Destination
- Ablauf des JWT

Session Tokens sind kurzlebig. Deshalb holt das Frontend für jeden API-Request und auch für den PDF-Download ein frisches Token über App Bridge. Das PDF wird nicht per normaler Browser-Navigation geladen, sondern per `fetch()` als Blob heruntergeladen.

Der Zugriff auf die Admin API passiert serverseitig über `ShopifyAdminService`.

## Gutschein-Erstellung

Die App nutzt die GraphQL Mutation `giftCardCreate`.

Dabei werden übergeben:

- Betrag
- Währung `EUR`
- optionales Ablaufdatum
- optionale interne Notiz

Die interne Notiz bleibt intern. Sie erscheint nicht in Vorschau, PDF oder Standardtemplate.

Shopify liefert in der Antwort den vollständigen Code zurück. Dieser Code wird direkt für die PDF-Daten verwendet und lokal verschlüsselt gespeichert.

## Shopify Dateien

Das Frontend öffnet den nativen Shopify File Picker über:

```js
shopify.intents.invoke('pick:shopify/File', {
    data: {
        mediaTypes: ['MediaImage'],
        multiSelect: false
    }
});
```

Nach der Auswahl wird nur die File-ID ans Backend geschickt. Das Backend fragt die Datei über GraphQL ab und akzeptiert sie nur, wenn sie ein `MediaImage` mit Status `READY` ist.

## Grenzen

Shopify gibt den vollständigen Gutscheincode später nicht erneut aus. Deshalb kann die App für selbst erstellte Gutscheine ein PDF erneut erzeugen, für alte Gutscheine ohne lokal gespeicherten Code aber nicht.

Ich schreibe auch keinen Fake-Eintrag in Shopifys Aktivitätsprotokoll. Wenn später eine Admin Extension für Gift Cards sauber verfügbar ist, kann dort ein eigener App-Block oder eine Aktion ergänzt werden.
