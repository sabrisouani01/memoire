document.addEventListener('click', function (e) {

    /* ===============================
       VIEW ORDER / REPAIR (EYE ICON)
    ================================ */
    const viewBtn = e.target.closest('.orders-btn.view, .tp-btn.view');
    if (viewBtn) {
        const targetId = viewBtn.dataset.target;
        const row = document.getElementById(targetId);
        if (!row) return;
        document.querySelectorAll('[id^="details-"],[id^="detail-"],[id^="mydetail-"],[id^="rep-"]').forEach(r => {
            if (r !== row) r.style.display = 'none';
        });
        row.style.display =
            row.style.display === 'none' || row.style.display === ''
                ? 'table-row' : 'none';
    }

    /* ===============================
       STATUS CHANGE (old admin orders page)
    ================================ */
    const statusSelect = e.target.closest('.order-status-select');
    if (statusSelect) {
        fetch('orders/update_status.php', {
            method: 'POST',
            body: new URLSearchParams({ id: statusSelect.dataset.id, status: statusSelect.value })
        });
    }

    /* ===============================
       ACCEPT ORDER
    ================================ */
    const acceptBtn = e.target.closest('.tp-accept-btn');
    if (acceptBtn) {
        const id = acceptBtn.dataset.id;
        if (!id) return;
        acceptBtn.disabled = true;
        acceptBtn.textContent = 'Accepting…';
        let reloadUrl = acceptBtn.dataset.reload || 'orders/index.php';
        if (!reloadUrl.endsWith('.php')) reloadUrl += '.php';
        fetch('orders/index.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ accept_id: id })
        })
        .then(r => r.text())
        .then(msg => {
            if (msg.trim() === 'success') { loadPage(reloadUrl); }
            else { acceptBtn.disabled = false; acceptBtn.textContent = 'Accept'; }
        })
        .catch(() => { acceptBtn.disabled = false; acceptBtn.textContent = 'Accept'; });
    }

    /* ===============================
       DELETE SERVICE — POST (delete.php requires POST)
    ================================ */
    const deleteBtn = e.target.closest('.tp-service-delete-btn');
    if (deleteBtn) {
        if (!confirm('Remove this service?')) return;
        const id = deleteBtn.dataset.id;
        fetch('services/delete.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ id: id })
        })
        .then(r => r.text())
        .then(() => loadPage('services/index.php'));
    }

});

/* ===============================
   STATUS CHANGE — tp-status-select (technician pages)
================================ */
document.addEventListener('change', function (e) {
    const sel = e.target.closest('.tp-status-select');
    if (!sel) return;
    const url = sel.dataset.url || 'orders/update_status.php';
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ id: sel.dataset.id, status: sel.value })
    })
    .then(r => r.text())
    .then(msg => {
        if (msg.trim() === 'success') {
            const row = sel.closest('tr');
            const badge = row ? row.querySelector('.tp-badge') : null;
            if (badge) {
                const labels = { pending:'Pending', in_progress:'In Progress', completed:'Completed', unrepairable:'Unrepairable', cancelled:'Cancelled' };
                const map    = { pending:'badge-pending', in_progress:'badge-inprogress', completed:'badge-completed', unrepairable:'badge-unrepairable', cancelled:'badge-cancelled' };
                badge.textContent = labels[sel.value] || sel.value;
                badge.className   = 'tp-badge ' + (map[sel.value] || '');
            }
            if (sel.value === 'cancelled') {
                const detailRow = row ? row.nextElementSibling : null;
                if (detailRow && detailRow.classList.contains('tp-detail-row')) detailRow.remove();
                if (row) row.remove();
            }
        }
    });
});

/* ===============================
   ADD SERVICE FORM
   (inline scripts don't execute inside innerHTML)
================================ */
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.id !== 'addServiceForm') return;
    e.preventDefault();
    const msg = document.getElementById('addServiceMsg');
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Saving…';
    fetch('services/add.php', { method: 'POST', body: new FormData(form) })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === 'success') {
            document.getElementById('addServiceModal').style.display = 'none';
            form.reset();
            loadPage('services/index.php');
        } else {
            msg.style.cssText = 'display:block;background:#fee2e2;color:#dc2626;padding:8px 12px;border-radius:8px;';
            msg.textContent = res || 'Something went wrong.';
            btn.disabled = false; btn.textContent = '+ Add Service';
        }
    })
    .catch(() => {
        if (msg) { msg.style.display = 'block'; msg.textContent = 'Server error.'; }
        btn.disabled = false; btn.textContent = '+ Add Service';
    });
});

/* ===============================
   ADD ORDER FORM (modal in orders/index.php)
================================ */
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.id !== 'addOrderForm') return;
    e.preventDefault();
    const msg = document.getElementById('addOrderMsg');
    const btn = document.getElementById('addOrderSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';
    fetch('orders/add_order.php', { method: 'POST', body: new FormData(form) })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === 'success') {
            document.getElementById('addOrderModal').style.display = 'none';
            form.reset();
            loadPage('orders/index.php');
        } else {
            msg.style.cssText = 'display:block;background:#fef2f2;color:#dc2626;padding:8px 12px;border-radius:8px;';
            msg.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + (res || 'Something went wrong.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-plus"></i> Create Order';
        }
    })
    .catch(() => {
        msg.style.cssText = 'display:block;background:#fef2f2;color:#dc2626;padding:8px 12px;border-radius:8px;';
        msg.textContent = 'Server error. Please try again.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Create Order';
    });
});
