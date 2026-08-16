<!DOCTYPE html>
<html lang="de">
<head>
    <meta name="shopify-api-key" content="{{ config('services.shopify.client_id') }}">
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gift Card App</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Gift Card App</h1>

    <p>
        Create customizable Shopify gift cards and export them as PDF.
    </p>

    <p id="app-bridge-status">
        Checking Shopify authentication...
    </p>

    <hr>

    <h2>Create gift card</h2>

<form id="gift-card-form">

    <p>
        <label for="amount">Amount</label><br>
        <input
            id="amount"
            type="number"
            min="0.01"
            step="0.01"
            value="10.00"
            required
        >
        EUR
    </p>

    <p>
        <label for="expires_on">Expires on</label><br>
        <input
            id="expires_on"
            type="date"
        >
    </p>

    <p>
        <label for="note">Internal note</label><br>
        <input
            id="note"
            type="text"
            maxlength="255"
            placeholder="Optional"
        >
    </p>

    <button type="submit">
        Create gift card
    </button>

</form>

<div id="gift-card-result" style="margin-top: 24px;"></div>

</div>

<script>
    const statusElement = document.getElementById('app-bridge-status');
    const giftCardForm = document.getElementById('gift-card-form');
    const giftCardResult = document.getElementById('gift-card-result');

    giftCardForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    giftCardResult.textContent = 'Creating gift card...';

    const payload = {
        amount: document.getElementById('amount').value,
        expires_on: document.getElementById('expires_on').value || null,
        note: document.getElementById('note').value || null,
    };

    try {
        const response = await fetch('/api/gift-cards', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            console.error(data);

            giftCardResult.textContent =
                'Gift card creation failed ❌';

            return;
        }

        giftCardResult.innerHTML = '';

        const heading = document.createElement('h3');
        heading.textContent = 'Gift card created ✅';

        const code = document.createElement('p');
        code.textContent = `Code: ${data.code}`;

        const id = document.createElement('p');
        id.textContent = `Shopify ID: ${data.gift_card.id}`;

        giftCardResult.appendChild(heading);
        giftCardResult.appendChild(code);
        giftCardResult.appendChild(id);

    } catch (error) {
        console.error(error);

        giftCardResult.textContent =
            'Gift card request failed ❌';
    }
});

    async function checkShopifyConnection() {
        if (typeof window.shopify === 'undefined') {
            statusElement.textContent = 'App Bridge not loaded ❌';
            return;
        }

        try {
            const sessionResponse = await fetch('/api/session-check');
            const sessionData = await sessionResponse.json();

            if (!sessionResponse.ok || !sessionData.authenticated) {
                statusElement.textContent =
                    `Shopify authentication failed ❌ (${sessionData.message ?? 'Unknown error'})`;
                return;
            }

            const shopResponse = await fetch('/api/shop-check');
            const shopData = await shopResponse.json();

            if (shopResponse.ok && shopData.shopify) {
                statusElement.textContent =
                    `Shopify API connected ✅ (${shopData.shopify.name} / ${shopData.shopify.myshopifyDomain})`;
            } else {
                statusElement.textContent =
                    'Shopify API connection failed ❌';

                console.error(shopData);
            }
        } catch (error) {
            console.error(error);

            statusElement.textContent =
                'Shopify API request failed ❌';
        }
    }

    checkShopifyConnection();
</script>

</body>
</html>