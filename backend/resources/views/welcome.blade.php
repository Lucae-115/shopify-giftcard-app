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
        Checking App Bridge...
    </p>

    <hr>

    <h2>Create gift card</h2>

    <p>The gift card form will be implemented here.</p>

</div>
<script>
    const statusElement = document.getElementById('app-bridge-status');

    if (typeof window.shopify !== 'undefined') {
        statusElement.textContent = 'App Bridge loaded ✅';
    } else {
        statusElement.textContent = 'App Bridge not loaded ❌';
    }
</script>
</body>
</html>