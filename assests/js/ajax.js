/* ================================
   loadPage — load fragment into #content
================================ */
function loadPage(url) {
    if (!url) return;
    const content = document.getElementById("content");
    if (!content) return;
    content.innerHTML = '<p style="padding:20px;color:#888;">Loading...</p>';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(html => {
            content.innerHTML = html;
            /* Re-execute <script> tags */
            content.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                [...oldScript.attributes].forEach(a => newScript.setAttribute(a.name, a.value));
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            if (typeof initAddProduct === 'function' && document.getElementById('imgDropzone')) {
                initAddProduct();
            }
        })
        .catch(err => { content.innerHTML = '<p style="padding:20px;color:#e74c3c;">Page not found: ' + err.message + '</p>'; });
}

/* ================================
   Sidebar navigation
================================ */
document.addEventListener("click", function (e) {
    const li = e.target.closest(".sidebar-menu li");
    if (!li) return;
    document.querySelectorAll(".sidebar-menu li").forEach(x => x.classList.remove("active"));
    li.classList.add("active");
    loadPage(li.dataset.page);
});

/* ================================
   Load dashboard on first visit
================================ */
document.addEventListener("DOMContentLoaded", function () {
    loadPage("dashboard/dashbord.php");
});

/* ================================
   Generic AJAX form (#addForm or .ajax-form)
   — stays in layout, shows result message
================================ */
document.addEventListener("submit", function (e) {
    if (!e.target || e.target.tagName !== "FORM") return;
    if (e.target.id !== "addForm" && !e.target.classList.contains("ajax-form")) return;
    if (e.target.method.toLowerCase() !== "post") return;
    e.preventDefault();
    const formData = new FormData(e.target, e.submitter || null);

    /* ── If this is the product add form, replace the file input data
          with the JS-managed fileList (DataTransfer trick is unreliable
          across browsers when the form is inside an AJAX-loaded fragment) ── */
    if (document.getElementById('imgDropzone')) {
        formData.delete('images[]');
        const files = window._addProductFileList || [];
        files.forEach(f => formData.append('images[]', f, f.name));
    }
    fetch(e.target.action || window.location.href, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        const box = document.getElementById("formMessage");
        if (box) { box.innerHTML = msg; box.style.display = "block"; }
        if (msg.trim().toLowerCase() === "success") {
            e.target.reset();
            loadPage("dashboard/dashbord.php");
        }
        /* ── Product add form: reset after Arabic/English success ── */
        const isProductSuccess = msg.includes('تم إضافة') || msg.includes('Account created successfully');
        if (isProductSuccess) {
            e.target.reset();
            /* clear image previews + file list */
            window._addProductFileList = [];
            const grid = document.getElementById('imgPreviewGrid');
            if (grid) grid.innerHTML = '';
            const fi = document.getElementById('imagesInput');
            if (fi) { try { fi.value = ''; } catch(_) {} }
            /* clear color chips */
            const colorDiv = document.getElementById('selectedColors');
            if (colorDiv) colorDiv.innerHTML = '';
            const colorJson = document.getElementById('colorsJsonInput');
            if (colorJson) colorJson.value = '';
            /* re-init so the dropzone works again */
            if (typeof initAddProduct === 'function' && document.getElementById('imgDropzone')) {
                initAddProduct();
            }
            /* auto-hide message after 3s */
            if (box) setTimeout(() => { box.style.display = 'none'; }, 3000);
        }
    })
    .catch(() => {
        const box = document.getElementById("formMessage");
        if (box) box.innerHTML = "Server error";
    });
});

/* ================================
   Edit product form — intercept submit,
   POST via fetch, stay in layout
================================ */
document.addEventListener("submit", function (e) {
    const form = e.target;
    if (!form || form.id !== "editForm") return;
    e.preventDefault();

    const content  = document.getElementById("content");
    const msgDiv   = document.getElementById("editMessage");
    const submitBtn = form.querySelector('[name="update"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }

    const formData = new FormData(form);
    fetch(form.action, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa-solid fa-file-pen"></i> حفظ التغييرات'; }
        /* show success banner inside the form page */
        if (msgDiv) {
            msgDiv.style.display  = 'block';
            msgDiv.style.background = msg.includes('تم') ? '#dcfce7' : '#fee2e2';
            msgDiv.style.color      = msg.includes('تم') ? '#166534' : '#991b1b';
            msgDiv.textContent      = msg || 'Done';
            setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
        }
        /* reload the same edit page to show updated images/data */
        const id = new URLSearchParams(form.action.split('?')[1]).get('id');
        if (id) setTimeout(() => loadPage('products/edit.php?id=' + id), 1200);
    })
    .catch(err => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa-solid fa-file-pen"></i> حفظ التغييرات'; }
        if (msgDiv) { msgDiv.style.display = 'block'; msgDiv.textContent = 'Error: ' + err.message; }
    });
});

/* ================================
   Product filters
================================ */
function loadProducts() {
    const search   = document.getElementById("searchInput")?.value   || "";
    const stock    = document.getElementById("filterStock")?.value   || "";
    const category = document.getElementById("filterCategory")?.value || "";
    fetch("/memoire/admin/products/fillter_products.php?" + new URLSearchParams({ search, stock, category }))
        .then(res => res.text())
        .then(html => { const box = document.getElementById("productsList"); if (box) box.innerHTML = html; });
}
document.addEventListener("input",  e => { if (e.target.id === "searchInput")                            loadProducts(); });
document.addEventListener("change", e => { if (e.target.id === "filterStock" || e.target.id === "filterCategory") loadProducts(); });

/* ================================
   Delete category — AJAX, no redirect
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-category");
    if (!btn) return;
    e.preventDefault();
    e.stopImmediatePropagation(); // prevent ajax-link handler from also firing
    if (!confirm("Delete this category?")) return;
    const id = btn.dataset.id;
    if (!id) return;

    fetch("/memoire/admin/categories/delete.php?id=" + encodeURIComponent(id), {
        redirect: 'manual'
    })
    .then(r => r.text().catch(() => ''))
    .then(text => {
        // If PHP returned an error message (Arabic or English), show it
        const trimmed = text.trim();
        if (trimmed && !trimmed.startsWith('<!') && trimmed.length < 300) {
            alert(trimmed);
        }
        loadPage('categories/index.php');
    })
    .catch(() => { loadPage('categories/index.php'); });
});

/* ================================
   AJAX links (data-page)
================================ */
document.addEventListener("click", function (e) {
    const link = e.target.closest(".ajax-link");
    if (!link) return;
    e.preventDefault();
    let url = link.dataset.page;
    if (!url) return;
    if (link.dataset.id) url += "?id=" + encodeURIComponent(link.dataset.id);
    loadPage(url);
});

/* ================================
   Delete product — AJAX, no redirect
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-product");
    if (!btn) return;
    e.preventDefault();
    if (!confirm("Delete this product?")) return;
    const id = btn.dataset.id;
    if (!id) return;

    /* optimistic: remove the row immediately */
    const row = btn.closest('.product-row');
    if (row) row.remove();

    fetch("/memoire/admin/products/delete.php?id=" + encodeURIComponent(id), {
        redirect: 'manual'
    })
    .then(r => r.json().catch(() => ({ok: true})))
    .then(data => {
        if (data && data.ok === false) {
            alert('Delete failed: ' + (data.msg || 'unknown error'));
            loadPage('products/index.php');
        } else {
            // Reload the full products page to keep CSS + search + filters intact
            loadPage('products/index.php');
        }
    })
    .catch(() => { loadPage('products/index.php'); });
});

/* ================================
   Delete customer
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-customer");
    if (!btn) return;
    if (!confirm("Delete this customer?")) return;
    if (!btn.dataset.id) return;
    fetch("/memoire/admin/customers/delete.php?id=" + btn.dataset.id)
        .then(() => { btn.closest("tr")?.remove(); });
});

/* ================================
   Filter tabs — Repairs / other pages
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".tp-filter-btn");
    if (!btn) return;
    document.querySelectorAll(".tp-filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    const filter     = btn.dataset.filter || "all";
    const activeLi   = document.querySelector(".sidebar-menu li.active");
    let   currentPage = activeLi ? (activeLi.dataset.page || "Repairs/index.php") : "Repairs/index.php";
    const base        = currentPage.split("?")[0];
    const basePath    = base.endsWith(".php") ? base : base + ".php";
    loadPage(basePath + "?filter=" + encodeURIComponent(filter));
    if (activeLi) activeLi.classList.add("active");
});
