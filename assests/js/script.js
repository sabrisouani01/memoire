/* ===== NAVBAR SCROLL EFFECT ===== */
const header = document.getElementById('top-header');
window.addEventListener('scroll', () => {
  header?.classList.toggle('scrolled', window.scrollY > 30);
});

/* ===== MOBILE MENU ===== */
const menu     = document.getElementById('menu');
const navLinks = document.getElementById('nav-links');
if (menu && navLinks) {
  menu.addEventListener('click', () => navLinks.classList.toggle('show'));
  document.addEventListener('click', e => {
    if (!menu.contains(e.target) && !navLinks.contains(e.target))
      navLinks.classList.remove('show');
  });
}

/* ===== ACTIVE NAV LINK ON SCROLL ===== */
const sections = document.querySelectorAll('section[id]');
const navAs    = document.querySelectorAll('.nav-links a');
const onScroll = () => {
  let current = '';
  sections.forEach(sec => {
    if (window.scrollY >= sec.offsetTop - 100) current = sec.id;
  });
  navAs.forEach(a => {
    a.classList.toggle('active', a.getAttribute('href') === '#' + current);
  });
};
window.addEventListener('scroll', onScroll);

/* ===== HERO SLIDER ===== */
const heroImgs = document.querySelectorAll('.hero-img');
const dots     = document.querySelectorAll('.dot');
let current = 0;
let sliderInterval;

function goToSlide(n) {
  heroImgs[current].classList.remove('active');
  dots[current]?.classList.remove('active');
  current = (n + heroImgs.length) % heroImgs.length;
  heroImgs[current].classList.add('active');
  dots[current]?.classList.add('active');
}

function startSlider() {
  sliderInterval = setInterval(() => goToSlide(current + 1), 5000);
}

document.getElementById('next-btn')?.addEventListener('click', () => {
  clearInterval(sliderInterval);
  goToSlide(current + 1);
  startSlider();
});
document.getElementById('prev-btn')?.addEventListener('click', () => {
  clearInterval(sliderInterval);
  goToSlide(current - 1);
  startSlider();
});
dots.forEach(dot => {
  dot.addEventListener('click', () => {
    clearInterval(sliderInterval);
    goToSlide(parseInt(dot.dataset.index));
    startSlider();
  });
});
startSlider();

/* ===== PRODUCT DETAILS TOGGLE ===== */
function toggleDetails(img) {
  const card    = img.closest('.product-card');
  const details = card.querySelector('.details');
  details.classList.toggle('show');
}

/* ===== CATEGORY FILTER PILLS ===== */
document.getElementById('cat-pills')?.addEventListener('click', e => {
  const pill = e.target.closest('.cat-pill');
  if (!pill) return;
  document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
  pill.classList.add('active');
  const cat = pill.dataset.cat;
  document.querySelectorAll('.product-card').forEach(card => {
    card.style.display = (!cat || card.dataset.category === cat) ? '' : 'none';
  });
});

/* ===== SEARCH MODAL ===== */
const searchModal = document.getElementById('search-modal');
document.getElementById('search-icon')?.addEventListener('click', e => {
  e.preventDefault();
  searchModal.style.display = 'flex';
});
document.getElementById('close-search')?.addEventListener('click', () => {
  searchModal.style.display = 'none';
});
searchModal?.addEventListener('click', e => {
  if (e.target === searchModal) searchModal.style.display = 'none';
});

/* ===== ADVANCED SEARCH ===== */
document.getElementById('search-form')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const term     = document.getElementById('search-term')?.value.toLowerCase() || '';
  const catId    = document.getElementById('category-filter')?.value || '';
  const minPrice = parseFloat(document.getElementById('min-price')?.value) || 0;
  const maxPrice = parseFloat(document.getElementById('max-price')?.value) || Infinity;

  searchModal.style.display = 'none';

  // Reset pills
  document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
  document.querySelector('.cat-pill[data-cat=""]')?.classList.add('active');

  document.querySelectorAll('.product-card').forEach(card => {
    const name  = card.querySelector('h3')?.textContent.toLowerCase() || '';
    const desc  = card.querySelector('.details p')?.textContent.toLowerCase() || '';
    const price = parseFloat(card.querySelector('.price')?.textContent.replace(/[^0-9.]/g, '')) || 0;
    const cat   = card.dataset.category || '';

    const ok = (name.includes(term) || desc.includes(term))
            && (!catId || cat === catId)
            && (price >= minPrice && price <= maxPrice);

    card.style.display = ok ? '' : 'none';
  });
});

/* ===== BUY BUTTON ===== */
// Handled by popup.js — openProductPopup() is called directly from onclick attributes.

/* ===== SCROLL REVEAL (simple fade-in) ===== */
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity    = '1';
      entry.target.style.transform  = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .service-card, .condition-card, .contact-card, .stat-item').forEach(el => {
  el.style.opacity   = '0';
  el.style.transform = 'translateY(24px)';
  el.style.transition= 'opacity .5s ease, transform .5s ease';
  observer.observe(el);
});
