/* ══════════════════════════════════════════════════════════════
   add_product.js  –  Image upload & colour picker for add.php
   Called by initAddProduct() after the page fragment is injected.
══════════════════════════════════════════════════════════════ */

function initAddProduct() {

    /* ══ IMAGE HANDLING ════════════════════════════════════════ */
    const dropzone    = document.getElementById('imgDropzone');
    const fileInput   = document.getElementById('imagesInput');
    const previewGrid = document.getElementById('imgPreviewGrid');

    if (!dropzone || !fileInput || !previewGrid) return; // guard: not on this page

    let fileList = [];

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        handleFiles([...e.dataTransfer.files]);
    });
    fileInput.addEventListener('change', () => {
        handleFiles([...fileInput.files]);
        fileInput.value = '';
    });

    function handleFiles(newFiles) {
        newFiles.forEach(f => {
            if (!f.type.startsWith('image/') || fileList.length >= 10) return;
            fileList.push(f);
        });
        renderPreviews();
        syncInput();
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        fileList.forEach((f, idx) => {
            const url  = URL.createObjectURL(f);
            const item = document.createElement('div');
            item.className = 'img-preview-item';
            item.innerHTML =
                `<img src="${url}" alt="">
                 ${idx === 0 ? '<span class="primary-badge">رئيسية</span>' : ''}
                 <button type="button" class="remove-img" data-idx="${idx}">
                     <i class="fa-solid fa-xmark"></i>
                 </button>`;
            previewGrid.appendChild(item);
        });

        previewGrid.querySelectorAll('.remove-img').forEach(btn =>
            btn.addEventListener('click', () => {
                fileList.splice(+btn.dataset.idx, 1);
                renderPreviews();
                syncInput();
            })
        );
    }

    function syncInput() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    /* ══ COLOR HANDLING ════════════════════════════════════════ */
    const picker      = document.getElementById('colorPicker');
    const labelInp    = document.getElementById('colorLabel');
    const stockInp    = document.getElementById('colorStock');
    const addBtn      = document.getElementById('addColorBtn');
    const selectedDiv = document.getElementById('selectedColors');
    const jsonInput   = document.getElementById('colorsJsonInput');

    if (!picker || !addBtn || !selectedDiv || !jsonInput) return;

    let colors = []; // [{hex, label, stock}, ...]

    addBtn.addEventListener('click', () =>
        addColor(picker.value, labelInp.value.trim(), +stockInp.value || 0)
    );

    document.querySelectorAll('.preset-swatch').forEach(sw =>
        sw.addEventListener('click', () =>
            addColor(sw.dataset.color, '', +stockInp.value || 0)
        )
    );

    function addColor(hex, label, stock) {
        hex = hex.toUpperCase();
        if (colors.find(c => c.hex === hex)) return;
        colors.push({ hex, label, stock });
        labelInp.value = '';
        stockInp.value = 0;
        render();
    }

    function render() {
        selectedDiv.innerHTML = '';
        colors.forEach((c, idx) => {
            const chip   = document.createElement('div');
            chip.className = 'color-chip';
            const isOut  = c.stock <= 0;
            chip.innerHTML =
                `<span class="chip-dot" style="background:${c.hex}"></span>
                 <div class="chip-info">
                     <span class="chip-hex">${c.hex}</span>
                     ${c.label ? `<span class="chip-label">${c.label}</span>` : ''}
                 </div>
                 <div class="chip-stock-wrap">
                     <label>الكمية:</label>
                     <input type="number" class="chip-stock-input" min="0"
                            value="${c.stock}" data-idx="${idx}">
                     <span class="stock-badge ${isOut ? 'out' : 'in'}">${isOut ? 'نفد' : 'متوفر'}</span>
                 </div>
                 <button type="button" class="chip-remove" data-idx="${idx}">×</button>`;
            selectedDiv.appendChild(chip);
        });

        // Stock input changes
        selectedDiv.querySelectorAll('.chip-stock-input').forEach(inp =>
            inp.addEventListener('input', () => {
                const i = +inp.dataset.idx;
                colors[i].stock = Math.max(0, +inp.value || 0);
                syncJSON();
                const badge       = inp.nextElementSibling;
                const val         = colors[i].stock;
                badge.textContent = val <= 0 ? 'نفد' : 'متوفر';
                badge.className   = `stock-badge ${val <= 0 ? 'out' : 'in'}`;
            })
        );

        // Remove buttons
        selectedDiv.querySelectorAll('.chip-remove').forEach(btn =>
            btn.addEventListener('click', () => {
                colors.splice(+btn.dataset.idx, 1);
                render();
            })
        );

        syncJSON();
    }

    function syncJSON() {
        jsonInput.value = colors.length ? JSON.stringify(colors) : '';
    }
}
