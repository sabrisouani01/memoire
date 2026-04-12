<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

$currentTech = $_SESSION['username'];

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone         = trim($_POST['phone']         ?? '');
    $item          = trim($_POST['item']          ?? '');
    $description   = trim($_POST['description']   ?? '');

    $errors = [];
    if ($customer_name === '') $errors[] = 'Customer name is required.';
    if ($phone === '')         $errors[] = 'Phone number is required.';
    if ($item === '')          $errors[] = 'Item / device is required.';

    if (!empty($errors)) {
        echo implode('<br>', $errors);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO repairs (customer_name, phone, item, description, status, technician, created_at)
        VALUES (?, ?, ?, ?, 'in_progress', ?, NOW())
    ");
    $stmt->execute([$customer_name, $phone, $item, $description, $currentTech]);

    echo 'success';
    exit;
}
?>
<script>
(function() {
    const form = document.getElementById('addOrderForm');
    const msg  = document.getElementById('addOrderMsg');
    const btn  = document.getElementById('addOrderSubmitBtn');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

        loadPage('orders/add_order', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'success') {
                msg.style.display      = 'block';
                msg.style.background   = '#f0fdf4';
                msg.style.color        = '#15803d';
                msg.innerHTML          = '<i class="fa-solid fa-circle-check"></i> Order created successfully! Redirecting…';
                form.reset();
                setTimeout(() => loadPage('orders/my_orders.php'), 1200);
            } else {
                msg.style.display    = 'block';
                msg.style.background = '#fef2f2';
                msg.style.color      = '#dc2626';
                msg.innerHTML        = '<i class="fa-solid fa-triangle-exclamation"></i> ' + (res || 'Something went wrong.');
                btn.disabled         = false;
                btn.innerHTML        = '<i class="fa-solid fa-plus"></i> Create Order';
            }
        })
        .catch(() => {
            msg.style.display    = 'block';
            msg.style.background = '#fef2f2';
            msg.style.color      = '#dc2626';
            msg.textContent      = 'Server error. Please try again.';
            btn.disabled         = false;
            btn.innerHTML        = '<i class="fa-solid fa-plus"></i> Create Order';
        });
    });
})();
</script>
