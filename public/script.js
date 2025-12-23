const menu = document.getElementById("menu");
const navLinks = document.getElementById("nav-links");

menu.addEventListener("click", () => {
  navLinks.classList.toggle("show");
});
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
function toggleDetails(img) {
    const card = img.closest('.product-card');
    const details = card.querySelector('.details');
    details.classList.toggle('show');
}