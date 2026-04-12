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
<div class="tp-page">
    <div class="tp-page-header">
        <h2 class="tp-title"><i class="fa-solid fa-plus-circle"></i> Add New Order</h2>
    </div>

    <div style="background:#fff;border-radius:18px;box-shadow:0 8px 24px rgba(0,0,0,0.07);padding:32px;max-width:560px;">
        <div id="addOrderMsg" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:14px;"></div>

        <form id="addOrderForm" style="display:flex;flex-direction:column;gap:18px;">

            <div>
                <label style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                    Customer Name <span style="color:#ef4444;">*</span>
                </label>
                <input name="customer_name" required placeholder="Full name"
                    style="width:100%;padding:11px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div>
                <label style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                    Phone Number <span style="color:#ef4444;">*</span>
                </label>
                <input name="phone" required placeholder="e.g. 0550 123 456"
                    style="width:100%;padding:11px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div>
                <label style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                    Item / Device <span style="color:#ef4444;">*</span>
                </label>
                <input name="item" required placeholder="e.g. iPhone 14, Samsung TV…"
                    style="width:100%;padding:11px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div>
                <label style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                    Description / Issue
                </label>
                <textarea name="description" rows="4" placeholder="Describe the problem or work needed…"
                    style="width:100%;padding:11px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;resize:vertical;box-sizing:border-box;transition:border-color .2s;font-family:inherit;"
                    onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            </div>

            <div style="display:flex;gap:12px;margin-top:4px;">
                <button type="submit" id="addOrderSubmitBtn"
                    style="flex:1;background:#2563eb;color:#fff;border:none;padding:13px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;transition:background .2s;">
                    <i class="fa-solid fa-plus"></i> Create Order
                </button>
                <button type="button" onclick="loadPage('orders/index.php')"
                    style="background:#f1f5f9;color:#64748b;border:none;padding:13px 20px;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
            </div>

        </form>
    </div>
</div>

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
        fetch('orders/add_order.php', {
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
