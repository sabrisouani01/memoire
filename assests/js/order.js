// STATUS CHANGE
document.querySelectorAll('.order-status-select').forEach(sel => {
    sel.addEventListener('change', () => {
        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=${sel.dataset.id}&status=${sel.value}'
        });
    });
});

// MODAL
const modal = document.getElementById('orderModal');
const content = document.getElementById('orderModalContent');

document.querySelectorAll('.orders-btn.view').forEach(btn => {
    btn.onclick = () => {
        modal.style.display = 'flex';
        fetch('order_view.php?id=' + btn.dataset.id)
            .then(r => r.text())
            .then(html => content.innerHTML = html);
    };
});

document.querySelector('.close-modal').onclick = () => {
    modal.style.display = 'none';
};