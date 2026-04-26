/* ===================================================
   WISE TECH — Product Popup + Cart Logic
   Works on both index.php and client/index.php
   =================================================== */

/* ── Helpers ── */
function showToast(msg, type = 'success') {
  const toast = document.getElementById('wt-toast');
  if (!toast) return;
  toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${msg}`;
  toast.className = `wt-toast ${type} show`;
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
}

function updateCartBadge() {
  const cart = JSON.parse(localStorage.getItem('wt_cart') || '[]');
  const total = cart.reduce((sum, i) => sum + i.qty, 0);
  document.querySelectorAll('.cart-badge').forEach(b => {
    b.textContent = total;
    b.style.display = total > 0 ? 'flex' : 'none';
  });
  // Also update legacy cart
  const legacyEl = document.querySelector('#cart-icon .cart-badge');
  if (legacyEl) { legacyEl.textContent = total; legacyEl.style.display = total > 0 ? 'flex' : 'none'; }
}

function addToCart(id, name, price, img, colorLabel, colorHex) {
  const cart = (function() { try { return JSON.parse(localStorage.getItem('wt_cart') || '[]'); } catch(e) { return []; } })();
  const existing = cart.find(i => String(i.id) === String(id) && (i.colorHex || '') === (colorHex || ''));
  if (existing) {
    existing.qty = (existing.qty || 1) + 1;
  } else {
    cart.push({ id, name, price: parseFloat(price), img, qty: 1, color: colorLabel || '', colorHex: colorHex || '' });
  }
  localStorage.setItem('wt_cart', JSON.stringify(cart));
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartBadge();
  showToast((typeof t === 'function' ? t('cart_added') : 'Added to cart') + ': ' + name, 'success');
}

/* ── Product Popup ── */
let currentPopupCard = null;

function openProductPopup(card) {
  currentPopupCard = card;
  const lang = (typeof currentLang !== 'undefined') ? currentLang : 'ar';

  const nameAr = card.dataset.nameAr || '';
  const nameFr = card.dataset.nameFr || '';
  const nameEn = card.dataset.nameEn || '';
  const descAr = card.dataset.descAr || '';
  const descFr = card.dataset.descFr || '';
  const descEn = card.dataset.descEn || '';
  const catAr  = card.dataset.categoryNameAr || '';
  const catFr  = card.dataset.categoryNameFr || '';
  const catEn  = card.dataset.categoryNameEn || '';

  // Pick correct language
  const name = lang === 'ar' ? nameAr : (lang === 'fr' ? (nameFr || nameEn || nameAr) : (nameEn || nameAr));
  const desc = lang === 'ar' ? descAr : (lang === 'fr' ? (descFr || descEn || descAr) : (descEn || descAr));
  const cat  = lang === 'ar' ? catAr  : (lang === 'fr' ? (catFr  || catEn  || catAr ) : (catEn  || catAr));

  const price   = parseFloat(card.dataset.price) || 0;
  const mainImg = card.dataset.img || '';
  const ram     = card.dataset.ram || '';
  const storage = card.dataset.storage || '';
  const camera  = card.dataset.camera || '';
  const currency = (typeof t === 'function') ? t('currency') : 'دج';
  const notSpec  = (typeof t === 'function') ? t('popup_not_specified') : '—';
  const noDesc   = (typeof t === 'function') ? t('popup_no_desc') : 'No description.';

  // Extra images
  let extraImages = [];
  try { extraImages = JSON.parse(card.dataset.extraImages || '[]'); } catch(e) {}

  // Colors
  let colors = [];
  try { colors = JSON.parse(card.dataset.colors || '[]'); } catch(e) {}

  // Set main image
  const mainImgEl = document.getElementById('popup-main-img');
  if (mainImgEl) { mainImgEl.src = mainImg; mainImgEl.alt = name; }

  // Thumbnails
  const thumbsEl = document.getElementById('popup-thumbs');
  if (thumbsEl) {
    thumbsEl.innerHTML = '';
    const allImgs = [mainImg, ...extraImages.filter(Boolean)];
    if (allImgs.length > 1) {
      allImgs.forEach((src, i) => {
        const img = document.createElement('img');
        img.src = src; img.alt = name;
        img.className = 'popup-thumb' + (i === 0 ? ' active' : '');
        img.addEventListener('click', () => {
          mainImgEl.style.opacity = '0';
          setTimeout(() => { mainImgEl.src = src; mainImgEl.style.opacity = '1'; }, 180);
          thumbsEl.querySelectorAll('.popup-thumb').forEach(t => t.classList.remove('active'));
          img.classList.add('active');
        });
        thumbsEl.appendChild(img);
      });
    }
  }

  // Category & name
  const catNameEl = document.getElementById('popup-category-name');
  if (catNameEl) catNameEl.textContent = cat || '—';

  const nameEl = document.getElementById('popup-name');
  if (nameEl) nameEl.textContent = name || '—';

  // Price
  const priceEl = document.getElementById('popup-price');
  if (priceEl) priceEl.innerHTML = `${price.toLocaleString('fr-DZ', {minimumFractionDigits:2})} <span>${currency}</span>`;

  // Specs
  const specsEl = document.getElementById('popup-specs');
  if (specsEl) {
    const specs = [];
    const ramLbl     = (typeof t === 'function') ? t('popup_ram')     : 'RAM';
    const storageLbl = (typeof t === 'function') ? t('popup_storage') : 'Storage';
    const cameraLbl  = (typeof t === 'function') ? t('popup_camera')  : 'Camera';
    if (ram)     specs.push([ramLbl,     ram]);
    if (storage) specs.push([storageLbl, storage]);
    if (camera)  specs.push([cameraLbl,  camera]);

    if (specs.length > 0) {
      specsEl.style.display = 'grid';
      specsEl.innerHTML = specs.map(([lbl, val]) => `
        <div class="popup-spec">
          <span class="popup-spec-label">${lbl}</span>
          <span class="popup-spec-value">${val || notSpec}</span>
        </div>
      `).join('');
    } else {
      specsEl.style.display = 'none';
      specsEl.innerHTML = '';
    }
  }

  // Colors
  const colorsRow = document.getElementById('popup-colors-row');
  const swatchesEl = document.getElementById('popup-swatches');
  if (colorsRow && swatchesEl) {
    if (colors.length > 0) {
      colorsRow.style.display = 'flex';
      swatchesEl.innerHTML = '';
      colors.forEach(c => {
        const hex   = typeof c === 'string' ? c : (c.hex || c.color || '#ccc');
        const label = typeof c === 'string' ? ''  : (c.label || c.name || '');
        const swatch = document.createElement('div');
        swatch.className = 'color-swatch';
        swatch.style.background = hex;
        swatch.dataset.hex = hex;   // keep original hex for cart
        swatch.title = label;
        swatch.innerHTML = label ? `<span class="color-swatch-label">${label}</span>` : '';
        swatch.addEventListener('click', () => {
          swatchesEl.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
          swatch.classList.add('selected');
        });
        swatchesEl.appendChild(swatch);
      });
    } else {
      colorsRow.style.display = 'none';
    }
  }

  // Description
  const descEl = document.getElementById('popup-desc-text');
  if (descEl) descEl.textContent = desc || noDesc;

  // Show popup
  const overlay = document.getElementById('product-popup-overlay');
  if (overlay) {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  // Apply i18n to popup elements
  if (typeof applyLang === 'function') applyLang(lang);
}

function closeProductPopup() {
  const overlay = document.getElementById('product-popup-overlay');
  if (overlay) {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
  currentPopupCard = null;
}

/* ── Popup event listeners ── */
document.addEventListener('DOMContentLoaded', () => {
  // Close button
  document.getElementById('popup-close')?.addEventListener('click', closeProductPopup);

  // Click outside popup
  document.getElementById('product-popup-overlay')?.addEventListener('click', e => {
    if (e.target === document.getElementById('product-popup-overlay')) closeProductPopup();
  });

  // ESC key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeProductPopup();
  });

  // Add to cart from popup
  document.getElementById('popup-add-cart-btn')?.addEventListener('click', () => {
    if (!currentPopupCard) return;
    const lang    = (typeof currentLang !== 'undefined') ? currentLang : 'ar';
    const nameAr  = currentPopupCard.dataset.nameAr || '';
    const nameFr  = currentPopupCard.dataset.nameFr || '';
    const nameEn  = currentPopupCard.dataset.nameEn || '';
    const name    = lang === 'ar' ? nameAr : (lang === 'fr' ? (nameFr || nameEn || nameAr) : (nameEn || nameAr));
    const id      = currentPopupCard.dataset.id || Date.now();
    const price   = currentPopupCard.dataset.price || 0;
    const img     = currentPopupCard.dataset.img || '';

    // Check login (if IS_LOGGED_IN is defined)
    if (typeof window.IS_LOGGED_IN !== 'undefined' && !window.IS_LOGGED_IN) {
      const msg = (typeof t === 'function') ? t('login_required') : 'Please sign in first.';
      showToast(msg, 'error');
      return;
    }

    // Get selected color from swatch
    const selectedSwatch = document.querySelector('#popup-swatches .color-swatch.selected');
    const selectedColorHex   = selectedSwatch ? (selectedSwatch.dataset.hex || '') : '';
    const selectedColorLabel = selectedSwatch ? (selectedSwatch.title || '') : '';
    addToCart(id, name, price, img, selectedColorLabel, selectedColorHex);

    // Animate button
    const btn = document.getElementById('popup-add-cart-btn');
    btn.classList.add('added');
    btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>' + ((typeof t === 'function') ? t('cart_added') : 'Added!') + '</span>';
    setTimeout(() => {
      btn.classList.remove('added');
      btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> <span data-i18n="popup_add_cart">' + ((typeof t === 'function') ? t('popup_add_cart') : 'Add to Cart') + '</span>';
    }, 2000);
  });

  // Add cart badge to cart icon
  const cartIconEl = document.getElementById('cart-icon');
  if (cartIconEl && !cartIconEl.querySelector('.cart-badge')) {
    const badge = document.createElement('span');
    badge.className = 'cart-badge';
    badge.style.display = 'none';
    cartIconEl.appendChild(badge);
  }

  updateCartBadge();
});

/* ── Listen to lang changes to update open popup ── */
document.addEventListener('langChanged', () => {
  if (currentPopupCard) openProductPopup(currentPopupCard);
});