/* header.js — dropdown toggle + responsive sidebar
   loadPage is defined in ajax.js, do NOT redefine it here */

function toggleDropdown(id) {
    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
        if (menu.id !== id) menu.style.display = 'none';
    });
    var el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

/* Close dropdowns when clicking outside */
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.style.display = 'none';
        });
    }
});

/* ── Responsive sidebar toggle ── runs after DOM is ready ── */
document.addEventListener('DOMContentLoaded', function() {
    var hamburger = document.getElementById('hamburgerBtn');
    var overlay   = document.getElementById('sidebarOverlay');
    var sidebar   = document.querySelector('.sidebar');

    if (!hamburger || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }

    hamburger.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    /* Close sidebar after clicking a nav item on mobile */
    sidebar.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && e.target.closest('li')) {
            setTimeout(closeSidebar, 150);
        }
    });

    /* Reset sidebar state on resize to desktop */
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});
