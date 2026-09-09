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

        * { box-sizing: border-box; }

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

        .topbar { margin-bottom: 20px; }

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

        .panel + .panel { margin-top: 16px; }

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
        select,
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
        select:focus,
        textarea:focus,
        button:focus {
            outline: 2px solid var(--focus);
            outline-offset: 1px;
        }

        .field.has-error input,
        .field.has-error select,
        .field.has-error textarea {
            border-color: var(--critical);
            background: #fff7f5;
        }

        .field-error {
            display: none;
            color: var(--critical);
            font-size: 12px;
            line-height: 1.35;
        }

        .field.has-error .field-error { display: block; }

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

        .primary:hover { background: var(--primary-strong); }

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

        .image-picker {
            position: relative;
            display: grid;
            place-items: center;
            width: 100%;
            min-height: 172px;
            border: 1px dashed #9da3a8;
            border-radius: 8px;
            padding: 14px;
            background: #fafbfb;
            cursor: pointer;
            overflow: hidden;
        }

        .image-picker:hover {
            border-color: var(--primary);
            background: #f3faf7;
        }

        .image-picker img {
            width: 100%;
            height: 172px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
            background: #dfe3e8;
        }

        .empty-image {
            display: grid;
            gap: 8px;
            justify-items: center;
            color: var(--muted);
            text-align: center;
        }

        .empty-image strong {
            color: var(--text);
            font-size: 14px;
        }

        .plus {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border: 1px solid #babfc3;
            border-radius: 999px;
            background: #ffffff;
            color: var(--primary);
            font-size: 26px;
            line-height: 1;
        }

        .remove-image {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            min-height: 30px;
            height: 30px;
            padding: 0;
            border: 1px solid rgba(32, 34, 35, .18);
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            color: var(--text);
            font-size: 20px;
            line-height: 1;
        }

        .image-caption {
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 14px;
            padding: 8px 10px;
            border-radius: 6px;
            background: rgba(0, 76, 63, .84);
            color: #ffffff;
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
            height: 720px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #ffffff;
            overflow: hidden;
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
            .app-shell { padding: 16px; }
            .layout { display: block; }
            .right-column { margin-top: 16px; }
        }

        @media (max-width: 620px) {
            .grid-2,
            .result-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main class="app-shell">
    <header class="topbar">
        <h1>PDF-Gutschein erstellen</h1>
        <p class="lead">
            Die App erstellt einen echten Shopify-Gutschein, nimmt den einmalig ausgegebenen Code
            und rendert daraus direkt ein druckbares PDF.
        </p>
    </header>

    <div class="layout">
        <section class="left-column">
            <form id="gift-card-form" class="panel" novalidate>
                <h2>Gutscheindaten</h2>

                <div class="grid-2">
                    <div class="field" data-field="amount">
                        <label for="amount">Betrag</label>
                        <input id="amount" type="number" min="0.01" step="0.01" value="25.00" required>
                        <span class="field-error"></span>
                    </div>

                    <div class="field">
                        <label for="currency">Währung</label>
                        <input id="currency" type="text" value="EUR" maxlength="3" readonly>
                    </div>
                </div>

                <div class="field" data-field="expires_preset">
                    <label for="expires_preset">Ablauf</label>
                    <select id="expires_preset">
                        <option value="none">Kein Ablaufdatum</option>
                        <option value="1_year">1 Jahr</option>
                        <option value="2_years">2 Jahre</option>
                        <option value="3_years">3 Jahre</option>
                        <option value="custom">Benutzerdefiniertes Datum</option>
                    </select>
                    <p id="expires-preview" class="help">Kein Ablaufdatum</p>
                    <span class="field-error"></span>
                </div>

                <div class="field" data-field="custom_expires_on" id="custom-expires-field" hidden>
                    <label for="custom_expires_on">Benutzerdefiniertes Ablaufdatum</label>
                    <input id="custom_expires_on" type="date">
                    <span class="field-error"></span>
                </div>

                <div class="field" data-field="note">
                    <label for="note">Interne Notiz</label>
                    <input id="note" type="text" maxlength="255" placeholder="z.B. Weihnachtsaktion 2026">
                    <p class="help">Wird nur intern bei Shopify gespeichert und erscheint nicht auf dem Gutschein.</p>
                    <span class="field-error"></span>
                </div>

                <div class="field" data-field="qr_url">
                    <label for="qr_url">QR-Code-Ziel</label>
                    <input id="qr_url" type="url" placeholder="https://dein-shop.myshopify.com">
                    <span class="field-error"></span>
                </div>

                <div class="field" data-field="image_url">
                    <label>Motiv aus Shopify Dateien</label>
                    <button id="image-picker" class="image-picker" type="button" aria-label="Bild aus Shopify auswählen"></button>
                    <span class="field-error"></span>
                </div>

                <div class="button-row">
                    <button id="create-button" class="primary" type="submit">Gutschein erstellen</button>
                    <button id="refresh-preview-button" class="secondary" type="button">Vorschau aktualisieren</button>
                </div>

                <div id="form-message" class="message" hidden></div>
            </form>

            <section class="panel">
                <h2>Vorlage bearbeiten</h2>
                <div class="field" data-field="template_html">
                    <label for="template_html">HTML Template</label>
                    <textarea id="template_html" spellcheck="false"></textarea>
                    <span class="field-error"></span>
                </div>

                <div class="field" data-field="template_css">
                    <label for="template_css">CSS</label>
                    <textarea id="template_css" spellcheck="false"></textarea>
                    <span class="field-error"></span>
                </div>

                <p class="help">Verfügbare Platzhalter:</p>
                <div class="placeholder-list" aria-label="Template-Platzhalter">
                    <code>@{{amount}}</code>
                    <code>@{{currency}}</code>
                    <code>@{{code}}</code>
                    <code>@{{expires_on}}</code>
                    <code>@{{qr_url}}</code>
                    <code>@{{display_qr_url}}</code>
                    <code>@{{qr_code}}</code>
                    <code>@{{image_url}}</code>
                    <code>@{{image_alt}}</code>
                    <code>@{{image_section}}</code>
                    <code>@{{expires_section}}</code>
                    <code>@{{qr_section}}</code>
                </div>
            </section>
        </section>

        <section class="right-column">
            <div class="panel">
                <h2>Gutschein-Vorschau</h2>
                <iframe id="voucher-preview" class="preview-frame" sandbox="allow-scripts" title="Gutschein-Vorschau"></iframe>
            </div>

            <div id="result-panel" class="panel" hidden>
                <h2>Ergebnis</h2>
                <div class="result-grid">
                    <div>
                        <p class="help">Vollständiger Shopify-Gutscheincode</p>
                        <p id="created-code" class="code"></p>
                    </div>
                    <a id="download-pdf" class="download-link" href="#" download>PDF herunterladen</a>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    const form = document.getElementById('gift-card-form');
    const message = document.getElementById('form-message');
    const createButton = document.getElementById('create-button');
    const refreshPreviewButton = document.getElementById('refresh-preview-button');
    const imagePicker = document.getElementById('image-picker');
    const previewFrame = document.getElementById('voucher-preview');
    const resultPanel = document.getElementById('result-panel');
    const createdCode = document.getElementById('created-code');
    const downloadPdf = document.getElementById('download-pdf');
    const templateHtml = document.getElementById('template_html');
    const templateCss = document.getElementById('template_css');
    const expiresPreset = document.getElementById('expires_preset');
    const customExpiresField = document.getElementById('custom-expires-field');
    const customExpiresInput = document.getElementById('custom_expires_on');
    const expiresPreview = document.getElementById('expires-preview');

    let selectedFile = null;
    let latestQrCode = '';
    let latestPdfUrl = '';
    let previewResizeTimer = null;

    function setMessage(text, type = '') {
        message.hidden = !text;
        message.textContent = text || '';
        message.className = `message ${type}`.trim();
    }

    async function authHeaders(extra = {}) {
        const headers = {...extra};

        if (window.shopify?.idToken) {
            const token = await window.shopify.idToken();
            headers.Authorization = `Bearer ${token}`;
        }

        return headers;
    }

    function formPayload(extra = {}) {
        return {
            amount: document.getElementById('amount').value,
            currency: document.getElementById('currency').value,
            expires_preset: expiresPreset.value,
            custom_expires_on: customExpiresInput.value || null,
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
            headers: await authHeaders({
                Accept: 'application/json',
                ...(options.body ? {'Content-Type': 'application/json'} : {}),
                ...(options.headers || {})
            })
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json() : null;

        return {response, data};
    }

    function clearErrors() {
        document.querySelectorAll('.field.has-error').forEach((field) => {
            field.classList.remove('has-error');
            const error = field.querySelector('.field-error');
            if (error) {
                error.textContent = '';
            }
        });
    }

    function showErrors(errors = {}) {
        clearErrors();

        Object.entries(errors).forEach(([fieldName, messages]) => {
            const field = document.querySelector(`[data-field="${fieldName}"]`);
            if (!field) {
                return;
            }

            field.classList.add('has-error');
            const error = field.querySelector('.field-error');
            if (error) {
                error.textContent = Array.isArray(messages) ? messages[0] : messages;
            }
        });
    }

    function extractError(data, fallback) {
        if (data?.errors && !Array.isArray(data.errors)) {
            showErrors(data.errors);
            return null;
        }

        if (Array.isArray(data?.errors)) {
            return data.errors.map((error) => error.message || error).join(' ');
        }

        return data?.message || fallback;
    }

    function dateAfterYears(years) {
        const date = new Date();
        date.setFullYear(date.getFullYear() + years);
        return date;
    }

    function formatDate(date) {
        return date.toLocaleDateString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function updateExpiresUi() {
        const value = expiresPreset.value;
        customExpiresField.hidden = value !== 'custom';

        if (value === 'none') {
            expiresPreview.textContent = 'Kein Ablaufdatum';
            customExpiresInput.value = '';
            return;
        }

        const presetYears = {
            '1_year': 1,
            '2_years': 2,
            '3_years': 3
        }[value];

        if (presetYears) {
            expiresPreview.textContent = `Gültig bis ${formatDate(dateAfterYears(presetYears))}`;
            customExpiresInput.value = '';
            return;
        }

        expiresPreview.textContent = customExpiresInput.value
            ? `Gültig bis ${formatDate(new Date(`${customExpiresInput.value}T00:00:00`))}`
            : 'Bitte ein Ablaufdatum auswählen.';
    }

    function setPreviewHtml(html) {
        const resizeScript = `
            <script>
                function fitVoucherPreview() {
                    const voucher = document.querySelector('.voucher') || document.body.firstElementChild || document.body;
                    document.documentElement.style.overflow = 'hidden';
                    document.body.style.overflow = 'hidden';
                    document.body.style.margin = '0';
                    document.body.style.transformOrigin = 'top left';
                    const width = voucher.offsetWidth || document.body.scrollWidth || 760;
                    const height = voucher.offsetHeight || document.body.scrollHeight || 700;
                    const scale = Math.min(1, Math.max(0.2, (window.innerWidth - 2) / width));
                    document.body.style.transform = 'scale(' + scale + ')';
                    window.parent.postMessage({
                        type: 'voucher-preview-size',
                        height: Math.ceil(height * scale) + 4
                    }, '*');
                }
                window.addEventListener('load', fitVoucherPreview);
                window.addEventListener('resize', fitVoucherPreview);
                setTimeout(fitVoucherPreview, 50);
            <\/script>
        `;

        previewFrame.srcdoc = html.includes('</body>')
            ? html.replace('</body>', `${resizeScript}</body>`)
            : `${html}${resizeScript}`;
    }

    window.addEventListener('message', (event) => {
        if (event.data?.type !== 'voucher-preview-size') {
            return;
        }

        clearTimeout(previewResizeTimer);
        previewResizeTimer = setTimeout(() => {
            previewFrame.style.height = `${Math.max(420, event.data.height)}px`;
        }, 20);
    });

    async function checkShopifyConnection() {
        if (typeof window.shopify === 'undefined') {
            setMessage('App Bridge wurde nicht geladen. Bitte die App im Shopify Admin öffnen.', 'error');
            return;
        }

        try {
            const {response, data} = await apiFetch('/api/shop-check');
            if (!response.ok || !data?.shopify) {
                setMessage(data?.message || 'Die Shopify-Verbindung konnte nicht geprüft werden.', 'error');
            }
        } catch (error) {
            console.error(error);
            setMessage('Die Shopify-Verbindung konnte nicht geprüft werden.', 'error');
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
        clearErrors();
        const {response, data} = await apiFetch('/api/gift-card-templates/preview', {
            method: 'POST',
            body: JSON.stringify(formPayload({
                code: createdCode.textContent || 'ABCD-1234-EFGH-5678'
            }))
        });

        if (!response.ok || !data?.success) {
            const validation = extractError(data, 'Vorschau konnte nicht erzeugt werden.');
            if (validation) {
                throw new Error(validation);
            }
            return;
        }

        latestQrCode = data.qr_code || '';
        setPreviewHtml(data.preview_html);
    }

    function renderImagePicker() {
        imagePicker.textContent = '';

        if (!selectedFile) {
            const empty = document.createElement('span');
            empty.className = 'empty-image';
            empty.innerHTML = '<span class="plus">+</span><strong>Bild aus Shopify auswählen</strong><span>Vorhandenes Bild aus Inhalte / Dateien verwenden</span>';
            imagePicker.appendChild(empty);
            return;
        }

        const image = document.createElement('img');
        image.src = selectedFile.url;
        image.alt = '';
        imagePicker.appendChild(image);

        if (selectedFile.alt) {
            const caption = document.createElement('span');
            caption.className = 'image-caption';
            caption.textContent = selectedFile.alt;
            imagePicker.appendChild(caption);
        }

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'remove-image';
        remove.setAttribute('aria-label', 'Bild entfernen');
        remove.textContent = '×';
        remove.addEventListener('click', async (event) => {
            event.stopPropagation();
            selectedFile = null;
            renderImagePicker();
            await refreshPreview();
        });
        imagePicker.appendChild(remove);
    }

    async function openFilePicker() {
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
                throw new Error(result.message || 'Es wurde kein Bild ausgewählt.');
            }

            const {response, data} = await apiFetch('/api/shopify-files/resolve', {
                method: 'POST',
                body: JSON.stringify({file_id: result.data.ids[0]})
            });

            if (!response.ok || !data?.success) {
                throw new Error(data?.message || 'Shopify-Bild konnte nicht gelesen werden.');
            }

            selectedFile = data.file;
            renderImagePicker();
            await refreshPreview();
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Die Shopify-Dateiauswahl konnte nicht geöffnet werden.', 'error');
        }
    }

    imagePicker.addEventListener('click', openFilePicker);

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

    [expiresPreset, customExpiresInput].forEach((input) => {
        input.addEventListener('change', async () => {
            updateExpiresUi();
            try {
                await refreshPreview();
            } catch (error) {
                console.error(error);
            }
        });
    });

    ['amount', 'qr_url', 'template_html', 'template_css'].forEach((id) => {
        document.getElementById(id).addEventListener('input', () => {
            const field = document.querySelector(`[data-field="${id}"]`);
            field?.classList.remove('has-error');
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setMessage('');
        clearErrors();
        createButton.disabled = true;
        createButton.textContent = 'Wird erstellt...';

        try {
            const {response, data} = await apiFetch('/api/gift-cards', {
                method: 'POST',
                body: JSON.stringify(formPayload())
            });

            if (!response.ok || !data?.success) {
                const validation = extractError(data, 'Der Gutschein konnte nicht erstellt werden.');
                if (validation) {
                    throw new Error(validation);
                }
                return;
            }

            latestQrCode = data.qr_code || '';
            setPreviewHtml(data.preview_html);
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
                headers: await authHeaders({Accept: 'application/pdf'})
            });

            if (!response.ok) {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const data = await response.json();
                    throw new Error(data.message || 'Das PDF konnte nicht heruntergeladen werden.');
                }
                throw new Error('Das PDF konnte nicht heruntergeladen werden.');
            }

            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const temporaryLink = document.createElement('a');
            temporaryLink.href = url;
            temporaryLink.download = `gutschein-${Date.now()}.pdf`;
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
        renderImagePicker();
        updateExpiresUi();

        try {
            await loadTemplateDefaults();
            await refreshPreview();
        } catch (error) {
            console.error(error);
            setMessage(error.message || 'Die App konnte nicht vollständig geladen werden.', 'error');
        }

        checkShopifyConnection();
    }

    boot();
</script>
</body>
</html>
