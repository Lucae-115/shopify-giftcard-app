<!DOCTYPE html>
<html lang="de">
<head>
    <meta name="shopify-api-key" content="{{ config('services.shopify.client_id') }}">
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify PDF-Gutscheine</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f6f7;
            --surface: #ffffff;
            --border: #d2d5d8;
            --text: #202223;
            --muted: #6d7175;
            --primary: #008060;
            --primary-strong: #006e52;
            --critical: #d82c0d;
            --focus: #458fff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        .app-shell {
            width: min(1440px, 100%);
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: clamp(24px, 3vw, 34px);
            line-height: 1.2;
            letter-spacing: 0;
        }

        .lead {
            margin: 0;
            max-width: 760px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.5;
        }

        .status-pill {
            min-width: 220px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--muted);
            font-size: 13px;
            text-align: right;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(340px, 430px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px;
        }

        .panel + .panel {
            margin-top: 16px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 17px;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .field {
            display: grid;
            gap: 6px;
            margin-bottom: 14px;
        }

        label {
            font-weight: 650;
            font-size: 13px;
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid #aeb4b9;
            border-radius: 6px;
            padding: 10px 11px;
            background: #ffffff;
            color: var(--text);
            font: inherit;
            font-size: 14px;
        }

        textarea {
            min-height: 170px;
            resize: vertical;
            font-family: "Cascadia Mono", Consolas, monospace;
            font-size: 12px;
            line-height: 1.45;
        }

        input:focus,
        textarea:focus,
        button:focus {
            outline: 2px solid var(--focus);
            outline-offset: 1px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .help {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 16px;
        }

        button,
        .download-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 8px 14px;
            cursor: pointer;
            font: inherit;
            font-weight: 650;
            text-decoration: none;
            transition: background .15s ease, border-color .15s ease;
        }

        .primary {
            background: var(--primary);
            color: #ffffff;
        }

        .primary:hover {
            background: var(--primary-strong);
        }

        .secondary,
        .download-link {
            background: #ffffff;
            color: var(--text);
            border-color: #babfc3;
        }

        .secondary:hover,
        .download-link:hover {
            border-color: #8c9196;
            background: #f6f6f7;
        }

        button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .image-preview {
            display: grid;
            grid-template-columns: 86px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            min-height: 96px;
            border: 1px dashed #babfc3;
            border-radius: 8px;
            padding: 10px;
            background: #fafbfb;
        }

        .image-preview img {
            width: 86px;
            height: 72px;
            object-fit: cover;
            border-radius: 6px;
            background: #dfe3e8;
        }

        .empty-thumb {
            width: 86px;
            height: 72px;
            border-radius: 6px;
            background: #dfe3e8;
        }

        .image-preview p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .message {
            margin-top: 14px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #ffffff;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.45;
        }

        .message.error {
            border-color: #ffd6cc;
            background: #fff4f4;
            color: var(--critical);
        }

        .message.success {
            border-color: #b4e1cf;
            background: #effaf5;
            color: #005e46;
        }

        .preview-frame {
            width: 100%;
            min-height: 700px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #ffffff;
        }

        .result-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            margin-top: 14px;
        }

        .code {
            margin: 4px 0 0;
            font-family: "Cascadia Mono", Consolas, monospace;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .placeholder-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }

        .placeholder-list code {
            border-radius: 4px;
            background: #eef0f2;
            padding: 3px 6px;
            font-size: 12px;
        }

        @media (max-width: 980px) {
            .app-shell {
                padding: 16px;
            }

            .topbar,
            .layout {
                display: block;
            }

            .status-pill {
                margin-top: 14px;
                text-align: left;
            }

            .right-column {
                margin-top: 16px;
            }
        }

        @media (max-width: 620px) {
            .grid-2,
            .result-grid {
                grid-template-columns: 1fr;
            }

            .preview-frame {
                min-height: 520px;
            }
        }
    </style>
</head>
<body>
<main class="app-shell">
    <header class="topbar">
        <div>
            <h1>PDF-Gutschein erstellen</h1>
            <p class="lead">
                Die App erstellt einen echten Shopify-Gutschein, nimmt den einmalig ausgegebenen Code
                und rendert daraus direkt ein druckbares PDF.
            </p>
        </div>
        <div id="app-bridge-status" class="status-pill">Shopify-Verbindung wird geprueft...</div>
    </header>

    <div class="layout">
        <section class="left-column">
            <form id="gift-card-form" class="panel">
                <h2>Gutscheindaten</h2>

                <div class="grid-2">
                    <div class="field">
                        <label for="amount">Betrag</label>
                        <input id="amount" type="number" min="0.01" step="0.01" value="25.00" required>
                    </div>

                    <div class="field">
                        <label for="currency">Waehrung</label>
                        <input id="currency" type="text" value="EUR" maxlength="3" readonly>
                    </div>
                </div>

                <div class="field">
                    <label for="expires_on">Ablaufdatum</label>
                    <input id="expires_on" type="date">
                    <p class="help">Leer lassen, wenn der Gutschein kein Ablaufdatum haben soll.</p>
                </div>

                <div class="field">
                    <label for="note">Interne Notiz</label>
                    <input id="note" type="text" maxlength="255" placeholder="z.B. Weihnachtsaktion 2026">
                </div>

                <div class="field">
                    <label for="qr_url">QR-Code-Ziel</label>
                    <input id="qr_url" type="url" placeholder="https://dein-shop.myshopify.com">
                </div>

                <div class="field">
                    <label>Motiv aus Shopify Dateien</label>
                    <div id="image-preview" class="image-preview">
                        <div class="empty-thumb"></div>
                        <p>Noch kein Bild ausgewaehlt.</p>
                    </div>
                    <div class="button-row">
                        <button id="select-image-button" class="secondary" type="button">
                            Bild aus Shopify auswaehlen
                        </button>
                    </div>
                </div>

                <div class="button-row">
                    <button id="create-button" class="primary" type="submit">Gutschein erstellen</button>
                    <button id="refresh-preview-button" class="secondary" type="button">Vorschau aktualisieren</button>
                </div>

                <div id="form-message" class="message" hidden></div>
            </form>

            <section class="panel">
                <h2>Vorlage bearbeiten</h2>
                <div class="field">
                    <label for="template_html">HTML Template</label>
                    <textarea id="template_html" spellcheck="false"></textarea>
                </div>

                <div class="field">
                    <label for="template_css">CSS</label>
                    <textarea id="template_css" spellcheck="false"></textarea>
                </div>

                <p class="help">Verfuegbare Platzhalter:</p>
                <div class="placeholder-list" aria-label="Template-Platzhalter">
                    <code>@{{amount}}</code>
                    <code>@{{currency}}</code>
                    <code>@{{code}}</code>
                    <code>@{{expires_on}}</code>
                    <code>@{{note}}</code>
                    <code>@{{qr_url}}</code>
                    <code>@{{qr_code}}</code>
                    <code>@{{image_url}}</code>
                    <code>@{{image_alt}}</code>
                </div>
            </section>
        </section>

        <section class="right-column">
            <div class="panel">
                <h2>Gutschein-Vorschau</h2>
                <iframe id="voucher-preview" class="preview-frame" sandbox title="Gutschein-Vorschau"></iframe>
            </div>

            <div id="result-panel" class="panel" hidden>
                <h2>Ergebnis</h2>
                <div class="result-grid">
                    <div>
                        <p class="help">Vollstaendiger Shopify-Gutscheincode</p>
                        <p id="created-code" class="code"></p>
                    </div>
                    <a id="download-pdf" class="download-link" href="#" download>PDF herunterladen</a>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    const statusElement = document.getElementById('app-bridge-status');
    const form = document.getElementById('gift-card-form');
    const message = document.getElementById('form-message');
    const createButton = document.getElementById('create-button');
    const refreshPreviewButton = document.getElementById('refresh-preview-button');
    const selectImageButton = document.getElementById('select-image-button');
    const imagePreview = document.getElementById('image-preview');
    const previewFrame = document.getElementById('voucher-preview');
    const resultPanel = document.getElementById('result-panel');
    const createdCode = document.getElementById('created-code');
    const downloadPdf = document.getElementById('download-pdf');
    const templateHtml = document.getElementById('template_html');
    const templateCss = document.getElementById('template_css');

    let selectedFile = null;
    let latestQrCode = '';
    let latestPdfUrl = '';

    function setMessage(text, type = '') {
        message.hidden = !text;
        message.textContent = text || '';
        message.className = `message ${type}`.trim();
    }

    function formPayload(extra = {}) {
        return {
            amount: document.getElementById('amount').value,
            currency: document.getElementById('currency').value,
            expires_on: document.getElementById('expires_on').value || null,
            note: document.getElementById('note').value || null,
            qr_url: document.getElementById('qr_url').value || null,
            qr_code: latestQrCode || null,
            file_id: selectedFile?.id || null,
            image_url: selectedFile?.url || null,
            image_alt: selectedFile?.alt || null,
            template_html: templateHtml.value,
            template_css: templateCss.value,
            ...extra
        };
    }

    async function apiFetch(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(options.body ? {'Content-Type': 'application/json'} : {}),
                ...(options.headers || {})
            }
        });

        const contentType = response.headers.get('content-type') || '';

        if (contentType.includes('application/json')) {
            const data = await response.json();
            return {response, data};
        }

        return {response, data: null};
    }

    async function checkShopifyConnection() {
        if (typeof window.shopify === 'undefined') {
            statusElement.textContent = 'App Bridge nicht geladen';
            return;
        }

        try {
            const {response, data} = await apiFetch('/api/shop-check');

            if (response.ok && data?.shopify) {
                statusElement.textContent = `${data.shopify.name} verbunden`;
                return;
            }

            statusElement.textContent = data?.message || 'Shopify-Verbindung fehlgeschlagen';
        } catch (error) {
            console.error(error);
            statusElement.textContent = 'Shopify-Verbindung fehlgeschlagen';
        }
    }

    async function loadTemplateDefaults() {
        const {response, data} = await apiFetch('/api/gift-card-templates/default');

        if (!response.ok) {
            throw new Error(data?.message || 'Template konnte nicht geladen werden.');
        }

        templateHtml.value = data.html;
        templateCss.value = data.css;
    }

    async function refreshPreview() {
        const {response, data} = await apiFetch('/api/gift-card-templates/preview', {
            method: 'POST',
            body: JSON.stringify(formPayload({
                code: createdCode.textContent || 'ABCD-1234-EFGH-5678'
            }))
        });

        if (!response.ok || !data?.success) {
            throw new Error(data?.message || 'Vorschau konnte nicht erzeugt werden.');
        }

        previewFrame.srcdoc = data.preview_html;
    }

    async function resolveSelectedFile(fileId) {
        const {response, data} = await apiFetch('/api/shopify-files/resolve', {
            method: 'POST',
            body: JSON.stringify({file_id: fileId})
        });

        if (!response.ok || !data?.success) {
            throw new Error(data?.message || 'Shopify-Bild konnte nicht gelesen werden.');
        }

        selectedFile = data.file;
        imagePreview.textContent = '';

        const image = document.createElement('img');
        image.src = selectedFile.url;
        image.alt = '';

        const copy = document.createElement('p');
        const title = document.createElement('strong');
        title.textContent = selectedFile.alt || 'Shopify-Bild';
        copy.appendChild(title);
        copy.appendChild(document.createElement('br'));
        copy.appendChild(document.createTextNode(selectedFile.id));

        imagePreview.appendChild(image);
        imagePreview.appendChild(copy);

        await refreshPreview();
    }

    selectImageButton.addEventListener('click', async () => {
        setMessage('');

        try {
            const activity = await shopify.intents.invoke('pick:shopify/File', {
                data: {
                    mediaTypes: ['MediaImage'],
                    multiSelect: false
                }
            });

            const result = await activity.complete;

            if (result.code === 'closed') {
                return;
            }

            if (result.code !== 'ok' || !result.data?.ids?.length) {
                throw new Error(result.message || 'Es wurde kein Bild ausgewaehlt.');
            }

            await resolveSelectedFile(result.data.ids[0]);
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Die Shopify-Dateiauswahl konnte nicht geoeffnet werden.', 'error');
        }
    });

    refreshPreviewButton.addEventListener('click', async () => {
        setMessage('');
        refreshPreviewButton.disabled = true;

        try {
            await refreshPreview();
        } catch (error) {
            console.error(error);
            setMessage(error.message, 'error');
        } finally {
            refreshPreviewButton.disabled = false;
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setMessage('');
        createButton.disabled = true;
        createButton.textContent = 'Wird erstellt...';

        try {
            const {response, data} = await apiFetch('/api/gift-cards', {
                method: 'POST',
                body: JSON.stringify(formPayload())
            });

            if (!response.ok || !data?.success) {
                const validation = data?.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : data?.message;

                throw new Error(validation || 'Der Gutschein konnte nicht erstellt werden.');
            }

            latestQrCode = data.qr_code || '';
            previewFrame.srcdoc = data.preview_html;
            createdCode.textContent = data.code;
            downloadPdf.href = data.pdf_url;
            latestPdfUrl = data.pdf_url;
            resultPanel.hidden = false;
            setMessage('Gutschein wurde erstellt. Das PDF kann jetzt heruntergeladen werden.', 'success');
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Der Gutschein konnte nicht erstellt werden.', 'error');
        } finally {
            createButton.disabled = false;
            createButton.textContent = 'Gutschein erstellen';
        }
    });

    downloadPdf.addEventListener('click', async (event) => {
        event.preventDefault();

        if (!latestPdfUrl) {
            return;
        }

        const originalText = downloadPdf.textContent;
        downloadPdf.textContent = 'PDF wird geladen...';

        try {
            const response = await fetch(latestPdfUrl, {
                headers: {
                    Accept: 'application/pdf'
                }
            });

            if (!response.ok) {
                throw new Error('Das PDF konnte nicht heruntergeladen werden.');
            }

            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const temporaryLink = document.createElement('a');
            temporaryLink.href = url;
            temporaryLink.download = `gift-card-${Date.now()}.pdf`;
            document.body.appendChild(temporaryLink);
            temporaryLink.click();
            temporaryLink.remove();
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Das PDF konnte nicht heruntergeladen werden.', 'error');
        } finally {
            downloadPdf.textContent = originalText;
        }
    });

    async function boot() {
        try {
            await loadTemplateDefaults();
            await refreshPreview();
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Die App konnte nicht vollstaendig geladen werden.', 'error');
        }

        checkShopifyConnection();
    }

    boot();
</script>
</body>
</html>
