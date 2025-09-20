<?php
require 'config.php';
require 'discount_engine.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Calculate discounts securely
$result = apply_discounts_to_cart_pdo($pdo, $uid);
if (!$result['ok']) {
    die("❌ Error applying discounts: " . htmlspecialchars($result['error']));
}

$items       = $result['items'];
$subtotal    = $result['subtotal'];
$discount    = $result['discount_total'];
$finalTotal  = $result['final_total'];
$quizPercent = $result['quiz_percent'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="css/style.css" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />
<title>Checkout - Online Inventory</title>
<style>
body { font-family: 'Fredoka', sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
nav { display: flex; justify-content: space-between; align-items: center; background-color: #4CAF50; padding: 10px 20px; }
nav a { color: white; text-decoration: none; margin: 0 10px; }
nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
nav ul li { margin: 0 10px; }

main { padding: 50px 20px; min-height: 80vh; }
.checkout_box { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.checkout_box h2 { text-align: center; margin-bottom: 20px; font-weight: 400; color: #333; }
.checkout_box p { margin: 10px 0; color: #333; }
.checkout_box hr { margin: 20px 0; }
.checkout_box input, .checkout_box select, .checkout_box textarea { margin: 8px 0; width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
.checkout_box button { display: block; margin: 20px auto 0; padding: 12px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-align: center; }
.checkout_box button:hover { background-color: #45a049; }
label img { margin-right: 5px; vertical-align: middle; height: 24px; }
#bkashFields { display: none; margin-top: 10px; }
</style>
</head>
<body>
<header>
<nav>
    <div class="nav_logo"><h1><a href="index.php">Online Inventory</a></h1></div>
    <ul class="nav_link">
        <li><a href="index.php">Home</a></li>
        <li><a href="products.php">Products</a></li>
        <li><a href="cart.php">Cart</a></li>
    </ul>
</nav>
</header>
<main>
<div class="checkout_box">
    <h2>Checkout</h2>
    <hr>
    <p><strong>Subtotal:</strong> <?= number_format($subtotal, 2) ?></p>
    <p><strong>Discount Applied:</strong> <?= number_format($discount, 2) ?> (<?= $quizPercent ?>%)</p>
    <p><strong>Final Total:</strong> <?= number_format($finalTotal, 2) ?></p>

    <form id="paymentForm" method="POST" action="submit_payment.php">
        <label>Payment Method:</label>
        <select name="payment_method" id="method" required onchange="showBkashFields(this.value)">
            <option value="">--Select--</option>
            <option value="Bkash">Bkash</option>
            <option value="CashOnDelivery">Cash on Delivery</option>
            <option value="Card">Card</option>
        </select>

        <div id="bkashFields">
            <label>Bkash Number:</label>
            <input type="text" name="bkash_number" placeholder="Enter Bkash Number" pattern="[0-9]{11}">

            <label>Bkash PIN:</label>
            <input type="password" name="bkash_pin" placeholder="Enter PIN" maxlength="6">
        </div>

        <label>Shipping Address:</label>
        <textarea name="shipping_address" rows="3" required></textarea>

        <button type="submit">Pay Now</button>
    </form>

    <script>
    function showBkashFields(value) {
        document.getElementById('bkashFields').style.display = value === 'Bkash' ? 'block' : 'none';
    }
    </script>

    <p>Want to add products? <a href="products.php">Browse Products</a></p>
    <p>Want to view cart before payment? <a href="cart.php">My Cart</a></p>
</div>
</main>
</body>
</html>
