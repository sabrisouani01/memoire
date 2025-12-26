function toggleDropdown(id) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.id !== id) menu.style.display = 'none';
    });

    const el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

// إغلاق عند الضغط خارج القائمة
document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});
function loadPage(page){
    fetch(page + ".php")
        .then(res => {
            if (!res.ok) throw new Error("Not Found");
            return res.text();
        })
        .then(html => {
            document.getElementById("content").innerHTML = html;
        })
        .catch(() =>{
            document.getElementById("content").innerHTML =
                "<p>Page not found</p>";
        });
}
