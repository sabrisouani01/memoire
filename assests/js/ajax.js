/* ================================
   تحميل الصفحة (layout)
================================ */
function loadPage(url) {
    if (!url) return;

    const content = document.getElementById("content");
    if (!content) return;

    content.innerHTML = "<p>Loading...</p>";

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
   submit method
================================ */
document.addEventListener("submit", function (e) {

   
    if (!e.target || e.target.tagName !== "FORM") return;

    
    if (e.target.id !== "addForm" && !e.target.classList.contains("ajax-form")) return;

    if (e.target.method.toLowerCase() !== "post") return;

    e.preventDefault();

    // Use e.submitter so submit-button name/value (e.g. "accept") is included in FormData
    const formData = new FormData(e.target, e.submitter || null);

    fetch(e.target.action || window.location.href, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData
    })
    .then(res => res.text())
    .then(msg => {
        const box = document.getElementById("formMessage");
        if (box) {
            box.innerHTML = msg;
            box.style.display = "block";
        }

        if (msg.trim().toLowerCase() === "success") {
            e.target.reset();
            // Refresh the dashboard after a successful accept action
            if (e.target.classList.contains("ajax-form") || e.target.id === "addForm") {
                loadPage("dashboard/dashbord.php");
            }
        }
    })
    .catch(() => {
        const box = document.getElementById("formMessage");
        if (box) box.innerHTML = "Server error";
    });
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

    const page = link.dataset.page;
    const id = link.dataset.id;

    let url = page;
    if (id) {
        url += "?id=" + encodeURIComponent(id);
    }

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
            loadProducts(); 
         });
});
/* ================================
   حذف Customer (AJAX)
================================ */
document.addEventListener("click", function (e) {

    const btn = e.target.closest(".delete-customer");
    if (!btn) return;

    if (!confirm("Delete this customer?")) return;

    const id = btn.dataset.id;
    if (!id) return;

    fetch("/memoire/admin/customers/delete.php?id=" + id)
        .then(res => res.text())
        .then(() => {
            btn.closest("tr").remove();
        });
});
/* ================================
   Repairs filter tabs (All / Active / Completed)
================================ */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".tp-filter-btn");
    if (!btn) return;

    // Update active state on the tabs
    document.querySelectorAll(".tp-filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    const filter = btn.dataset.filter || "all";

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