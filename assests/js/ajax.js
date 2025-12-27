/* ================================
   تحميل الصفحة (layout)
================================ */
function loadPage(page) {
    if (!page) return;

    const content = document.getElementById("content");
    if (!content) return;

    content.innerHTML = "<p>Loading...</p>";

    // فصل المسار عن الـ query
    let url = page;
    if (!page.endsWith(".php")) {
        url = page.includes("?") ? page : page + ".php";
    }

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error();
            return res.text();
        })
        .then(html => {
            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = "<p>Page not found</p>";
        });
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
   تحميل dashboard أول مرة
================================ */
document.addEventListener("DOMContentLoaded", function () {
    loadPage("dashboard/dashbord");
});

/* ================================
   فلترة المنتجات (AJAX فقط)
================================ */
function loadProducts() {
    const search = document.getElementById("searchInput")?.value || "";
    const stock = document.getElementById("filterStock")?.value || "";
    const category = document.getElementById("filterCategory")?.value || "";

    const params = new URLSearchParams({
        search,
        stock,
        category
    });

    fetch("/memoire/admin/products/fillter_products.php?" + params)
        .then(res => res.text())
        .then(html => {
            const box = document.getElementById("productsList");
            if (box) box.innerHTML = html;
        });
}

/* ================================
   events (delegation)
================================ */
document.addEventListener("input", e => {
    if (e.target.id === "searchInput") loadProducts();
});

document.addEventListener("change", e => {
    if (
        e.target.id === "filterStock" ||
        e.target.id === "filterCategory"
    ) {
        loadProducts();
    }
});
/* ================================
   ajax links (add / edit)
================================ */
document.addEventListener("click", function (e) {
    const link = e.target.closest(".ajax-link");
    if (!link) return;

    e.preventDefault();
    e.stopPropagation(); // ✅ هذا هو الإصلاح

    const page = link.dataset.page;
    const id = link.dataset.id;

    let url = page;
    if (id) url += "?id=" + id;

    loadPage(url);
});
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-product");
    if (!btn) return;

    if (!confirm("Delete this product?")) return;

    const id = btn.dataset.id;

    fetch("/memoire/admin/products/delete.php?id=" + id)
        .then(res => res.text())
        .then(() => {
            loadProducts(); // تحديث القائمة
        });
});