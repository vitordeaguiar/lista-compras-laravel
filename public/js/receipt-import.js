/* Importação de lista a partir do cupom fiscal.
 * Fluxo: 1 foto -> tenta QR Code (jsQR) -> se não houver NFC-e, cai no OCR (Tesseract)
 * -> backend extrai os itens -> tela de revisão editável -> cria a lista.
 *
 * Depende de jsQR e Tesseract carregados antes, e de window.CUPOM (extractUrl, csrf).
 */
(function () {
    'use strict';

    var cfg = window.CUPOM || {};
    var itemIndex = 0;

    var el = {
        file:        document.getElementById('cupomFile'),
        captureBtn:  document.getElementById('captureBtn'),
        captureCard: document.getElementById('captureCard'),
        status:      document.getElementById('statusBox'),
        statusText:  document.getElementById('statusText'),
        progress:    document.getElementById('progressFill'),
        error:       document.getElementById('errorBox'),
        form:        document.getElementById('reviewForm'),
        name:        document.getElementById('listName'),
        date:        document.getElementById('listDate'),
        items:       document.getElementById('itemsContainer'),
        count:       document.getElementById('itemCount'),
        addBtn:      document.getElementById('addItemBtn'),
    };

    if (!el.file) return; // não está na página de cupom

    // ── UI helpers ───────────────────────────────────────────────────────
    function showStatus(text, pct) {
        el.captureCard.hidden = true;
        el.error.hidden = true;
        el.status.hidden = false;
        el.statusText.textContent = text;
        el.progress.style.width = (pct == null ? 8 : Math.round(pct * 100)) + '%';
    }
    function showError(msg) {
        el.status.hidden = true;
        el.captureCard.hidden = false;
        el.error.hidden = false;
        el.error.textContent = msg;
    }
    function hide(node) { node.hidden = true; }

    // ── captura ──────────────────────────────────────────────────────────
    el.captureBtn.addEventListener('click', function () { el.file.click(); });
    el.file.addEventListener('change', function () {
        var file = el.file.files && el.file.files[0];
        if (file) handleFile(file);
    });

    function handleFile(file) {
        showStatus('Analisando a imagem…', 0.05);
        loadImage(file).then(function (img) {
            var url = tryDecodeQr(img);
            if (url) {
                showStatus('Consultando a NFC-e na SEFAZ…', null);
                return extract({ source: 'qr', url: url });
            }
            return runOcr(file);
        }).catch(function () {
            showError('Não consegui processar a imagem. Tente outra foto.');
        });
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            var url = URL.createObjectURL(file);
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function (e) { URL.revokeObjectURL(url); reject(e); };
            img.src = url;
        });
    }

    // ── QR Code ──────────────────────────────────────────────────────────
    function tryDecodeQr(img) {
        if (typeof jsQR !== 'function') return null;
        // Reduz a imagem para no máx. 1600px no maior lado (memória/performance)
        var max = 1600;
        var scale = Math.min(1, max / Math.max(img.width, img.height));
        var w = Math.round(img.width * scale);
        var h = Math.round(img.height * scale);
        var c = document.createElement('canvas');
        c.width = w; c.height = h;
        var ctx = c.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        var data = ctx.getImageData(0, 0, w, h);
        var code = jsQR(data.data, w, h, { inversionAttempts: 'attemptBoth' });
        // Só seguimos pelo QR se for de uma NFC-e de SP; senão deixamos pro OCR.
        if (code && code.data && /fazenda\.sp\.gov\.br/i.test(code.data)) {
            return code.data;
        }
        return null;
    }

    // ── OCR (Tesseract) ──────────────────────────────────────────────────
    function runOcr(file) {
        if (typeof Tesseract === 'undefined') {
            showError('Leitor de texto indisponível. Tente novamente.');
            return Promise.resolve();
        }
        showStatus('Lendo o texto do cupom…', 0.1);

        var worker;
        return Tesseract.createWorker('por', 1, {
            workerPath: '/vendor/tesseract/worker.min.js',
            corePath:   '/vendor/tesseract/',
            langPath:   '/vendor/tesseract/lang',
            workerBlobURL: false,
            logger: function (m) {
                if (m.status === 'recognizing text') {
                    showStatus('Lendo o texto do cupom…', m.progress);
                }
            },
        }).then(function (w) {
            worker = w;
            return worker.recognize(file);
        }).then(function (res) {
            return extract({ source: 'ocr', text: res.data.text });
        }).catch(function () {
            // Inclui o caso de o WebAssembly do OCR ser bloqueado/indisponível
            // (ex.: navegador antigo sem suporte a 'wasm-unsafe-eval').
            showError('Não consegui ler o texto do cupom neste dispositivo. Tente outra foto.');
        }).then(function () {
            // finally: encerra o worker em qualquer cenário (sucesso ou falha).
            if (worker) return worker.terminate();
        });
    }

    // ── chamada ao backend ───────────────────────────────────────────────
    function extract(payload) {
        return fetch(cfg.extractUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf,
            },
            body: JSON.stringify(payload),
        }).then(function (resp) {
            return resp.json().then(function (json) {
                if (!resp.ok) {
                    showError(json.message || 'Não consegui ler o cupom. Tente outra foto ou adicione os itens manualmente.');
                    return;
                }
                renderReview(json);
            });
        }).catch(function () {
            showError('Falha de conexão ao processar o cupom.');
        });
    }

    // ── revisão ──────────────────────────────────────────────────────────
    function renderReview(data) {
        hide(el.status);
        hide(el.captureCard);
        el.form.hidden = false;

        var today = new Date().toISOString().slice(0, 10);
        el.date.value = data.date || today;
        el.name.value = data.name || ('Compra ' + brDate(el.date.value));

        el.items.innerHTML = '';
        itemIndex = 0;
        (data.items || []).forEach(addRow);
        updateCount();
        if (!el.items.children.length) addRow({});
    }

    function addRow(item) {
        var i = itemIndex++;
        var row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML =
            '<input type="text" name="items[' + i + '][name]" class="it-name" placeholder="Item" required value="' + esc(item.name) + '">' +
            '<input type="number" step="0.001" min="0.001" name="items[' + i + '][qty]" class="it-qty" aria-label="Quantidade" value="' + numVal(item.qty, 1) + '">' +
            '<input type="text" name="items[' + i + '][unit]" class="it-unit" placeholder="un" aria-label="Unidade" value="' + esc(item.unit) + '">' +
            '<input type="number" step="0.01" min="0" name="items[' + i + '][price]" class="it-price" placeholder="0,00" aria-label="Preço" value="' + numVal(item.price, '') + '">' +
            '<button type="button" class="it-remove" aria-label="Remover item">&times;</button>';
        el.items.appendChild(row);
    }

    el.addBtn.addEventListener('click', function () { addRow({}); updateCount(); });
    el.items.addEventListener('click', function (e) {
        if (e.target.classList.contains('it-remove')) {
            e.target.closest('.item-row').remove();
            updateCount();
        }
    });

    function updateCount() {
        el.count.textContent = el.items.children.length;
    }

    // ── util ─────────────────────────────────────────────────────────────
    function esc(v) {
        if (v == null) return '';
        return String(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function numVal(v, fallback) {
        return (v == null || v === '') ? fallback : v;
    }
    function brDate(iso) {
        var p = (iso || '').split('-');
        return p.length === 3 ? p[2] + '/' + p[1] : iso;
    }
})();
