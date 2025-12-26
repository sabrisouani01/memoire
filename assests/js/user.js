const menu = document.getElementById("menu");
const navLinks = document.getElementById("nav-links");
const userTrigger = document.getElementById("user-menu-trigger");
const dropdown = document.getElementById("user-dropdown");
const searchIcon = document.querySelector('#search-icon');
const cartIcon = document.querySelector('#cart-icon');

// Mobile menu toggle
menu?.addEventListener("click", () => {
  navLinks.classList.toggle("show");
});

// Product details toggle
function toggleDetails(img) {
  const card = img.closest(".product-card");
  const details = card.querySelector(".details");
  details.classList.toggle("show");
}

// Set username from session/localStorage
function setUsername(name) {
  const usernameElement = document.getElementById("username");
  if (usernameElement) {
    usernameElement.textContent = name;
  }
}

// User dropdown toggle
if (userTrigger && dropdown) {
  userTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
  });

  document.addEventListener("click", (e) => {
    if (!userTrigger.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.style.display = "none";
    }
  });
}

// Search modal
const searchModal = document.getElementById("search-modal");
const closeSearch = document.getElementById("close-search");

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
    if (e.target === searchModal) searchModal.style.display = "none";
  });
}

// Search form (demo)
document.getElementById("search-form")?.addEventListener("submit", function(e) {
  e.preventDefault();
  
  const searchTerm = document.getElementById("search-term")?.value.toLowerCase() || '';
  const categoryFilter = document.getElementById("category-filter")?.value || '';
  const minPrice = parseFloat(document.getElementById("min-price")?.value) || 0;
  const maxPrice = parseFloat(document.getElementById("max-price")?.value) || Infinity;

  // Hide modal
  document.getElementById("search-modal").style.display = "none";

  // Get all product cards
  const productCards = document.querySelectorAll(".product-card");

  productCards.forEach(card => {
    const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
    const priceText = card.querySelector('.price')?.textContent || '0';
    const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
    const category = card.dataset.category || ''; // We'll add this in PHP

    const matchesSearch = name.includes(searchTerm);
    const matchesCategory = !categoryFilter || category === categoryFilter;
    const matchesPrice = price >= minPrice && price <= maxPrice;

    if (matchesSearch && matchesCategory && matchesPrice) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
});

// ✅ FIXED: Orders & Repairs links - Corrected paths
document.getElementById("orders-link")?.addEventListener("click", (e) => {
  e.preventDefault();
  const isLoggedIn = window.IS_LOGGED_IN || (localStorage.getItem("username") !== "زائر" && localStorage.getItem("username"));
  if (isLoggedIn) {
    // ✅ CORRECT PATH: client/orders/orders.php
    window.location.href = "../client/orders/orders.php";
  } else {
    alert("يرجى تسجيل الدخول لعرض طلباتك.");
    window.location.href = "../auth/login.php";
  }
});

document.getElementById("repairs-link")?.addEventListener("click", (e) => {
  e.preventDefault();
  const isLoggedIn = window.IS_LOGGED_IN || (localStorage.getItem("username") !== "زائر" && localStorage.getItem("username"));
  if (isLoggedIn) {
    // ✅ CORRECT PATH: client/repairs/repairs.php
    window.location.href = "../client/repairs/repairs.php";
  } else {
    alert("يرجى تسجيل الدخول لطلب صيانة.");
    window.location.href = "../auth/login.php";
  }
});

// Cart modal
const cartModal = document.getElementById("cart-modal");
const closeCart = document.getElementById("close-cart");

if (cartIcon && cartModal) {
  cartIcon.addEventListener("click", (e) => {
    e.preventDefault();
    loadCart();
    cartModal.style.display = "flex";
  });
}

if (closeCart) {
  closeCart.addEventListener("click", () => {
    cartModal.style.display = "none";
  });
}

if (cartModal) {
  cartModal.addEventListener("click", (e) => {
    if (e.target === cartModal) cartModal.style.display = "none";
  });
}

// Cart functions
function loadCart() {
  const list = document.getElementById("cart-items-list");
  const totalEl = document.getElementById("total-amount");
  const emptyMsg = document.getElementById("empty-cart-message");
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  if (cart.length === 0) {
    list.innerHTML = '';
    list.appendChild(emptyMsg);
    totalEl.textContent = "0 دج";
    return;
  }

  list.innerHTML = '';
  let total = 0;
  cart.forEach(item => {
    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <img src="${item.img}" alt="${item.name}">
      <div class="cart-item-details">
        <div class="cart-item-title">${item.name}</div>
        <div class="cart-item-price">${item.price} دج</div>
      </div>
      <div class="cart-item-controls">
        <div class="cart-item-quantity">
          <button class="minus" data-id="${item.id}">-</button>
          <input type="number" value="${item.qty}" min="1" data-id="${item.id}">
          <button class="plus" data-id="${item.id}">+</button>
        </div>
        <button class="cart-item-remove" data-id="${item.id}">&times;</button>
      </div>
    `;
    list.appendChild(div);
    total += item.price * item.qty;
  });
  totalEl.textContent = total.toFixed(2) + " دج";

  // Attach event listeners
  document.querySelectorAll('.minus').forEach(btn => {
    btn.addEventListener('click', (e) => updateQty(e.target.dataset.id, -1));
  });
  document.querySelectorAll('.plus').forEach(btn => {
    btn.addEventListener('click', (e) => updateQty(e.target.dataset.id, 1));
  });
  document.querySelectorAll('.cart-item-remove').forEach(btn => {
    btn.addEventListener('click', (e) => removeItem(e.target.dataset.id));
  });
  document.querySelectorAll('.cart-item-quantity input').forEach(input => {
    input.addEventListener('change', (e) => {
      let val = parseInt(e.target.value) || 1;
      updateQty(e.target.dataset.id, val - getQty(e.target.dataset.id));
    });
  });
}

function getQty(id) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let item = cart.find(i => i.id == id);
  return item ? item.qty : 1;
}

function updateQty(id, delta) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let item = cart.find(i => i.id == id);
  if (item) {
    item.qty += delta;
    if (item.qty < 1) item.qty = 1;
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
  }
}

function removeItem(id) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  cart = cart.filter(i => i.id != id);
  localStorage.setItem("cart", JSON.stringify(cart));
  loadCart();
}

// Add to cart (requires data-* attributes on buttons)
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("buy-btn")) {
    const btn = e.target;
    // Try to get data from attributes (updated product cards must have these)
    const id = btn.dataset.id || Date.now();
    const name = btn.dataset.name || btn.closest('.product-card')?.querySelector('h3')?.textContent || 'منتج';
    const price = parseFloat(btn.dataset.price) || parseFloat(btn.closest('.product-card')?.querySelector('.price')?.textContent?.replace(/[^0-9.]/g, '') || '0');
    const img = btn.dataset.img || btn.closest('.product-card')?.querySelector('img')?.src || '';

    const item = { id, name, price, img, qty: 1 };
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let existing = cart.find(i => i.id == item.id);
    if (existing) {
      existing.qty++;
    } else {
      cart.push(item);
    }
    localStorage.setItem("cart", JSON.stringify(cart));
    alert(`تمت إضافة ${name} إلى السلة!`);
  }
});

// ✅ FIXED: Cart checkout - Correct path to checkout.php
document.getElementById("checkout-btn")?.addEventListener("click", () => {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  if (cart.length === 0) {
    alert("السلة فارغة!");
    return;
  }

  // Create a hidden form and submit to checkout.php
  const form = document.createElement('form');
  form.method = 'POST';
  // ✅ CORRECT PATH: client/checkout.php (assuming checkout.php is directly in client/)
  form.action = '../client/checkout.php';

  // Add cart data as JSON
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'cart_data';
  input.value = JSON.stringify(cart);
  form.appendChild(input);

  document.body.appendChild(form);
  form.submit();
});

document.getElementById("clear-cart-btn")?.addEventListener("click", () => {
  if (confirm("هل أنت متأكد من تفريغ السلة؟")) {
    localStorage.removeItem("cart");
    loadCart();
  }
});

// Initialize on load
document.addEventListener("DOMContentLoaded", () => {
  // If PHP sets window.IS_LOGGED_IN, use session username
  // Otherwise, fall back to localStorage (for compatibility)
  let currentUser = "زائر";
  if (typeof window.IS_LOGGED_IN !== 'undefined' && window.IS_LOGGED_IN) {
    // Username is already set by PHP in index.php
  } else {
    currentUser = localStorage.getItem("username") || "زائر";
    setUsername(currentUser);
  }
});