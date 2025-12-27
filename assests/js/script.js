const menu = document.getElementById("menu");
const navLinks = document.getElementById("nav-links");
const searchIcon = document.getElementById("search-icon");
const searchModal = document.getElementById("search-modal");
const closeSearch = document.getElementById("close-search");

// Mobile menu toggle
if (menu && navLinks) {
  menu.addEventListener("click", () => {
    navLinks.classList.toggle("show");
  });
}

// Hero slider
let current = 1;
function changeHero() {
  const img1 = document.getElementById("img1");
  const img2 = document.getElementById("img2");
  if (current === 1) {
    img1.classList.remove("active");
    img2.classList.add("active");
    current = 2;
  } else {
    img2.classList.remove("active");
    img1.classList.add("active");
    current = 1;
  }
}

// Product details toggle
function toggleDetails(img) {
  const card = img.closest('.product-card');
  const details = card.querySelector('.details');
  details.classList.toggle('show');
}

// Search modal
if (searchIcon && searchModal) {
  searchIcon.addEventListener("click", (e) => {
    e.preventDefault();
    searchModal.style.display = "flex";
  });
}

if (closeSearch) {
  closeSearch.addEventListener("click", () => {
    searchModal.style.display = "none";
  });
}

if (searchModal) {
  searchModal.addEventListener("click", (e) => {
    if (e.target === searchModal) {
      searchModal.style.display = "none";
    }
  });
}

// ✅ ADVANCED SEARCH
document.getElementById("search-form")?.addEventListener("submit", function(e) {
  e.preventDefault();
  
  const searchTerm = document.getElementById("search-term")?.value.toLowerCase() || '';
  const categoryFilter = document.getElementById("category-filter")?.value || '';
  const minPrice = parseFloat(document.getElementById("min-price")?.value) || 0;
  const maxPrice = parseFloat(document.getElementById("max-price")?.value) || Infinity;

  searchModal.style.display = "none";

  document.querySelectorAll(".product-card").forEach(card => {
    const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
    const desc = card.querySelector('.details p')?.textContent.toLowerCase() || '';
    const priceText = card.querySelector('.price')?.textContent || '0';
    const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
    const category = card.dataset.category || '';

    const matches = 
      (name.includes(searchTerm) || desc.includes(searchTerm)) &&
      (!categoryFilter || category === categoryFilter) &&
      (price >= minPrice && price <= maxPrice);

    card.style.display = matches ? 'block' : 'none';
  });
});

// ✅ BUY BUTTON → LOGIN
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("buy-btn")) {
    alert("يرجى تسجيل الدخول لإتمام عملية الشراء.");
    window.location.href = "auth/login.php";
  }
});