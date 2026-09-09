# Shopify-Integration

## Authentifizierung

Die App laeuft eingebettet im Shopify Admin. Frontend-Requests an `/api/*` muessen ein Shopify Session Token enthalten. Laravel validiert dieses Token in `VerifyShopifySessionToken`.

Geprueft wird:

- Signatur mit `SHOPIFY_CLIENT_SECRET`
- `aud` gegen `SHOPIFY_CLIENT_ID`
- `iss` und `dest`
- gleicher Shop in Issuer und Destination
- Ablauf des JWT

Der Zugriff auf die Admin API passiert serverseitig ueber `ShopifyAdminService`.

## Gutschein-Erstellung

Die App nutzt die GraphQL Mutation `giftCardCreate`.

Dabei werden uebergeben:

- Betrag
- Waehrung `EUR`
- optionales Ablaufdatum
- optionale interne Notiz

Shopify liefert in der Antwort den vollstaendigen Code zurueck. Dieser Code wird direkt fuer die PDF-Daten verwendet und lokal verschluesselt gespeichert.

## Shopify Dateien

Das Frontend oeffnet den nativen Shopify File Picker ueber:

```js
shopify.intents.invoke('pick:shopify/File', {
    data: {
        mediaTypes: ['MediaImage'],
        multiSelect: false
    }
});
```

Nach der Auswahl wird nur die File-ID ans Backend geschickt. Das Backend fragt die Datei ueber GraphQL ab und akzeptiert sie nur, wenn sie ein `MediaImage` mit Status `READY` ist.

## Grenzen

Shopify gibt den vollstaendigen Gutscheincode spaeter nicht erneut aus. Deshalb kann die App fuer selbst erstellte Gutscheine ein PDF erneut erzeugen, fuer alte Gutscheine ohne lokal gespeicherten Code aber nicht.

Ich schreibe auch keinen Fake-Eintrag in Shopifys Aktivitaetsprotokoll. Wenn spaeter eine Admin Extension fuer Gift Cards sauber verfuegbar ist, kann dort ein eigener App-Block oder eine Aktion ergaenzt werden.
