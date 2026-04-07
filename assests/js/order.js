document.addEventListener('click', function (e) {

    /* ===============================
       VIEW ORDER (EYE ICON)
    ================================ */
    const viewBtn = e.target.closest('.orders-btn.view, .tp-btn.view');
    if (viewBtn) {

        const targetId = viewBtn.dataset.target;
        const row = document.getElementById(targetId);
        if (!row) return;

        // close others
        document.querySelectorAll('[id^="details-"],[id^="detail-"],[id^="mydetail-"],[id^="rep-"]').forEach(r => {
            if (r !== row) r.style.display = 'none';
        });

        row.style.display =
            row.style.display === 'none' || row.style.display === ''
                ? 'table-row'
                : 'none';
    }

    /* ===============================
       STATUS CHANGE (old admin orders page)
    ================================ */
    const statusSelect = e.target.closest('.order-status-select');
    if (statusSelect) {
        fetch('orders/update_status.php', {
            method: 'POST',
            body: new URLSearchParams({
                id: statusSelect.dataset.id,
                status: statusSelect.value
            })
        });
    }

});

/* ===============================
   STATUS CHANGE (new tp-status-select, technician pages)
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
            // Update the visible badge in the same row
            const row = sel.closest('tr');
            const badge = row ? row.querySelector('.tp-badge') : null;
            if (badge) {
                const labels = {
                    pending: 'Pending', in_progress: 'In Progress',
                    completed: 'Completed', unrepairable: 'Unrepairable', cancelled: 'Cancelled'
                };
                badge.textContent = labels[sel.value] || sel.value;
                badge.className = 'tp-badge';
                const map = {
                    pending:'badge-pending', in_progress:'badge-inprogress',
                    completed:'badge-completed', unrepairable:'badge-unrepairable',
                    cancelled:'badge-cancelled'
                };
                badge.classList.add(map[sel.value] || '');
            }
        }
    });
});

/* ===============================
   ACCEPT ORDER (technician claims a pending order)
================================ */
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.tp-accept-btn');
    if (!btn) return;

    const id = btn.dataset.id;
    if (!id) return;

    btn.disabled = true;
    btn.textContent = 'Accepting…';

    fetch('orders/index.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ accept_id: id })
    })
    .then(r => r.text())
    .then(msg => {
        if (msg.trim() === 'success') {
            loadPage('orders/index.php');
        } else {
            btn.disabled = false;
            btn.textContent = 'Accept';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Accept';
    });
});

/* ===============================
   DELETE SERVICE (technician removes a service)
================================ */
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.tp-service-delete-btn');
    if (!btn) return;

    if (!confirm('Delete this service?')) return;

    const id = btn.dataset.id;

    fetch('services/delete.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ id: id })
    })
    .then(r => r.text())
    .then(msg => {
        if (msg.trim() === 'success') {
            loadPage('services/index.php');
        }
    });
});
