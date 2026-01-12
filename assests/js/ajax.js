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

    
    if (e.target.id !== "addForm") return;

    
    if (e.target.method.toLowerCase() !== "post") return;

    e.preventDefault();

  
    fetch(e.target.action, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: new FormData(e.target)
    })
    .then(res => res.text())
    .then(msg => {
        const box = document.getElementById("formMessage");
        if (!box) return;

        box.innerHTML = msg;
        box.style.display = "block";

        if (msg.toLowerCase().includes("success")) {
            e.target.reset();
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