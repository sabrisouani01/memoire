/* ================================
   loadPage — load fragment into #content
================================ */
function loadPage(url) {
    if (!url) return;
    const content = document.getElementById("content");
    if (!content) return;
    content.innerHTML = '<p style="padding:20px;color:#888;">Loading...</p>';
    fetch(url)
        .then(res => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(html => { content.innerHTML = html; })
        .catch(() => { content.innerHTML = '<p style="padding:20px;color:#e74c3c;">Page not found</p>'; });
}

/* ================================
   sidebar navigation
================================ */
document.addEventListener("click", function (e) {
    const li = e.target.closest(".sidebar-menu li");
    if (!li) return;

    document
        .querySelectorAll(".sidebar-menu li")
        .forEach(x => x.classList.remove("active"));

    li.classList.add("active");

    loadPage(li.dataset.page);
});


/* ================================
   Load dashboard on first visit
================================ */
document.addEventListener("DOMContentLoaded", function () {
    loadPage("dashboard/dashbord");
});

/* ================================
   Generic AJAX form (#addForm or .ajax-form)
================================ */
document.addEventListener("submit", function (e) {
    if (!e.target || e.target.tagName !== "FORM") return;
    if (e.target.id !== "addForm" && !e.target.classList.contains("ajax-form")) return;
    if (e.target.method.toLowerCase() !== "post") return;
    e.preventDefault();
    const formData = new FormData(e.target, e.submitter || null);
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
            loadPage("dashboard/dashbord");
        }
    })
    .catch(() => {
        const box = document.getElementById("formMessage");
        if (box) box.innerHTML = "Server error";
    });
});

/* ================================
   Product filters
================================ */
function loadProducts() {
    const search   = document.getElementById("searchInput")?.value || "";
    const stock    = document.getElementById("filterStock")?.value || "";
    const category = document.getElementById("filterCategory")?.value || "";
    fetch("/memoire/admin/products/fillter_products.php?" + new URLSearchParams({ search, stock, category }))
        .then(res => res.text())
        .then(html => { const box = document.getElementById("productsList"); if (box) box.innerHTML = html; });
}

document.addEventListener("input",  e => { if (e.target.id === "searchInput") loadProducts(); });
document.addEventListener("change", e => { if (e.target.id === "filterStock" || e.target.id === "filterCategory") loadProducts(); });

/* ================================
   AJAX links (data-page)
================================ */
document.addEventListener("click", function (e) {
    const link = e.target.closest(".ajax-link");
    if (!link) return;
    e.preventDefault();
    let url = link.dataset.page;
    if (link.dataset.id) url += "?id=" + encodeURIComponent(link.dataset.id);
    loadPage(url);
});

/* ================================
   Delete product
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-product");
    if (!btn) return;
    if (!confirm("Delete this product?")) return;
    fetch("/memoire/admin/products/delete.php?id=" + btn.dataset.id)
        .then(() => loadProducts());
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
        .then(() => { btn.closest("tr").remove(); });
});
/* ================================
   Filter tabs — Repairs (All / Active / Completed)
   and any other page with .tp-filter-btn
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".tp-filter-btn");
    if (!btn) return;

    // Update active tab visually
    document.querySelectorAll(".tp-filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    const filter = btn.dataset.filter || "all";

    // Get the current page from the active sidebar item
    const activeLi   = document.querySelector(".sidebar-menu li.active");
    let   currentPage = activeLi ? (activeLi.dataset.page || "Repairs/index") : "Repairs/index";
    const base        = currentPage.split("?")[0];
    const basePath    = base.endsWith(".php") ? base : base + ".php";

    loadPage(basePath + "?filter=" + encodeURIComponent(filter));

    // Keep sidebar item highlighted after reload
    if (activeLi) activeLi.classList.add("active");
});

    // Re-use the same path the sidebar used to load this page
    const activeLi = document.querySelector(".sidebar-menu li.active");
    const currentPage = activeLi ? activeLi.dataset.page : "Repairs/index";
    // Ensure .php extension for filter URLs
    const base = currentPage.split("?")[0];
    const basePath = base.endsWith(".php") ? base : base + ".php";

    loadPage(basePath + "?filter=" + encodeURIComponent(filter));
    // Also keep sidebar item active
    if (activeLi) activeLi.classList.add("active");
});
