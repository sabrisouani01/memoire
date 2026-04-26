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

/* =====================================================
   CART — rewritten
   Key: wt_cart (synced with popup.js)
   Item shape: { id, name, price, img, qty, color, colorHex }
   ===================================================== */
const CART_KEY = 'wt_cart';
function getCart() { try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } catch(e) { return []; } }
function saveCart(cart) { localStorage.setItem(CART_KEY, JSON.stringify(cart)); localStorage.setItem('cart', JSON.stringify(cart)); updateCartBadge(); }
function updateCartBadge() {
  const total = getCart().reduce((s, i) => s + (i.qty || 1), 0);
  document.querySelectorAll('.cart-badge').forEach(b => { b.textContent = total; b.style.display = total > 0 ? 'flex' : 'none'; });
}

const cartModal = document.getElementById("cart-modal");
const closeCart = document.getElementById("close-cart");

if (cartIcon && cartModal) {
  cartIcon.addEventListener("click", (e) => { e.preventDefault(); renderCart(); cartModal.style.display = "flex"; });
}
if (closeCart) closeCart.addEventListener("click", () => { cartModal.style.display = "none"; });
if (cartModal) cartModal.addEventListener("click", (e) => { if (e.target === cartModal) cartModal.style.display = "none"; });

function renderCart() {
  const list    = document.getElementById("cart-items-list");
  const totalEl = document.getElementById("total-amount");
  if (!list || !totalEl) return;
  const cart = getCart();

  if (cart.length === 0) {
    list.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:180px;"><i class="fa-solid fa-cart-shopping" style="font-size:36px;color:#cbd5e1;"></i><p style="margin-top:12px;color:#94a3b8;font-size:14px;">السلة فارغة.</p></div>';
    totalEl.textContent = "0 دج";
    return;
  }

  let total = 0;
  list.innerHTML = '';
  cart.forEach(item => {
    const qty       = item.qty || 1;
    const price     = parseFloat(item.price) || 0;
    const itemTotal = price * qty;
    total += itemTotal;

    const colorBadge = item.colorHex
      ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;border-radius:20px;padding:2px 8px;font-size:11px;color:#475569;margin-bottom:3px;"><span style="width:10px;height:10px;border-radius:50%;background:' + item.colorHex + ';border:1px solid rgba(0,0,0,.12);display:inline-block;"></span>' + (item.color || '') + '</span>'
      : '';

    const imgEl = item.img
      ? '<img style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;flex-shrink:0;" src="' + item.img + '" alt="' + item.name + '" onerror="this.style.display=\'none\'">'
      : '<div style="width:64px;height:64px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-image" style="color:#cbd5e1;font-size:20px;"></i></div>';

    const div = document.createElement("div");
    div.className = "cart-item";
    div.dataset.id = item.id;
    div.style.cssText = "display:flex;align-items:flex-start;gap:12px;padding:12px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;";
    div.innerHTML = imgEl
      + '<div style="flex:1;min-width:0;">'
      + '<div style="font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;">' + item.name + '</div>'
      + colorBadge
      + '<div style="font-size:13px;color:#2563eb;font-weight:600;">' + price.toLocaleString('fr-DZ') + ' دج</div>'
      + '<div style="font-size:11px;color:#94a3b8;">= ' + itemTotal.toLocaleString('fr-DZ') + ' دج</div>'
      + '</div>'
      + '<div style="display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0;">'
      + '<div style="display:flex;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">'
      + '<button class="qty-minus" data-id="' + item.id + '" style="width:28px;height:28px;background:none;border:none;font-size:16px;cursor:pointer;color:#64748b;line-height:1;">−</button>'
      + '<span style="font-size:13px;font-weight:700;color:#1e293b;min-width:20px;text-align:center;">' + qty + '</span>'
      + '<button class="qty-plus" data-id="' + item.id + '" style="width:28px;height:28px;background:none;border:none;font-size:16px;cursor:pointer;color:#64748b;line-height:1;">+</button>'
      + '</div>'
      + '<button class="cart-item-remove" data-id="' + item.id + '" style="background:none;border:none;color:#cbd5e1;cursor:pointer;font-size:14px;padding:4px;" title="حذف"><i class="fa-solid fa-trash-can"></i></button>'
      + '</div>';
    list.appendChild(div);
  });

  totalEl.textContent = total.toLocaleString('fr-DZ') + " دج";
  list.querySelectorAll('.qty-minus').forEach(btn => btn.addEventListener('click', () => changeQty(btn.dataset.id, -1)));
  list.querySelectorAll('.qty-plus').forEach(btn  => btn.addEventListener('click', () => changeQty(btn.dataset.id, +1)));
  list.querySelectorAll('.cart-item-remove').forEach(btn => btn.addEventListener('click', () => removeCartItem(btn.dataset.id)));
}

function changeQty(id, delta) {
  const cart = getCart();
  const item = cart.find(i => String(i.id) === String(id));
  if (!item) return;
  item.qty = Math.max(1, (item.qty || 1) + delta);
  saveCart(cart); renderCart();
}
function removeCartItem(id) { saveCart(getCart().filter(i => String(i.id) !== String(id))); renderCart(); }

document.getElementById("clear-cart-btn")?.addEventListener("click", () => {
  if (confirm("هل أنت متأكد من تفريغ السلة؟")) { saveCart([]); renderCart(); }
});
document.getElementById("checkout-btn")?.addEventListener("click", () => {
  const cart = getCart();
  if (cart.length === 0) { alert("السلة فارغة!"); return; }
  const checkoutData = cart.map(item => ({
    id: item.id, name: item.name, price: item.price,
    qty: item.qty || 1, img: item.img || '',
    color: item.color || '', colorHex: item.colorHex || ''
  }));
  const form = document.createElement('form');
  form.method = 'POST'; form.action = 'checkout.php';
  const input = document.createElement('input');
  input.type = 'hidden'; input.name = 'cart_data';
  input.value = JSON.stringify(checkoutData);
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
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