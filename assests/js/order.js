document.addEventListener('click', function (e) {

    /* ===============================
       VIEW ORDER (EYE ICON)
    ================================ */
    const viewBtn = e.target.closest('.orders-btn.view');
    if (viewBtn) {

        const targetId = viewBtn.dataset.target;
        const row = document.getElementById(targetId);
        if (!row) return;

        // إغلاق باقي التفاصيل
        document.querySelectorAll('[id^="details-"]').forEach(r => {
            if (r !== row) r.style.display = 'none';
        });

        // فتح / إغلاق
        row.style.display =
            row.style.display === 'none' || row.style.display === ''
                ? 'table-row'
                : 'none';
    }

    /* ===============================
       STATUS CHANGE
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
