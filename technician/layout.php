<?php
require "includes/tech_auth.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin panel</title>
    <link rel="stylesheet" href="../assests/css/header.css">
    <link rel="stylesheet" href="../assests/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assests/css/admin_dashbord.css">
    <link rel="stylesheet" href="../assests/css/products.css">
    <link rel="stylesheet" href="../assests/css/add_product.css">
    <link rel="stylesheet" href="../assests/css/orders.css">
    <link rel="stylesheet" href="../assests/css/customers.css">
    <link rel="stylesheet" href="../assests/css/categorie.css">
    <link rel="stylesheet" href="../assests/css/add_cat.css">
    <link rel="stylesheet" href="../assests/css/technician_pages.css">
</head>
<style>
    .content{
        margin-left: 260px;
        margin-top: 64px;
        height: 64px;
        padding: 30px;
        min-height: 100vh;
        box-sizing: border-box;
    }
</style>
<body>
    <?php
    include "includes/header.php";
    include "includes/sidebar.php";?> 
    <div class="content" id="content"></div>
    <script src="../assests/js/ajax.js"></script>
    <script src="../assests/js/header.js"></script>
    <script src="../assests/js/order.js"></script>
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</body>
</html>