<?php
require 'config.php';
require 'discount_engine.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uid = $_SESSION['user_id'] ?? null;
if (!$uid) {
    header('Location: login.php');
    exit;
}

// Collect form data
$method  = $_POST['payment_method'] ?? '';
$address = trim($_POST['shipping_address'] ?? '');

if (!$method || !$address) {
    die("❌ Payment method and address are required.");
}

try {
    // Recalculate totals using discount engine
    $result = apply_discounts_to_cart_pdo($pdo, (int)$uid);
    if (!$result['ok']) {
        throw new Exception("Error applying discounts: " . $result['error']);
    }

    if (empty($result['items'])) {
        throw new Exception("Your cart is empty.");
    }

    $finalTotal = $result['final_total'];
    $orderDate  = date('Y-m-d H:i:s');

    // Insert new order
    $ins = $pdo->prepare("INSERT INTO `order`
        (Status, Shipping_Date, Shipping_Address, Order_date, UserID, TotalAmount, PaymentMethod)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([
        'Processing',
        $orderDate,   // placeholder shipping date = order date
        $address,
        $orderDate,
        $uid,
        $finalTotal,
        $method
    ]);
    $orderID = $pdo->lastInsertId();

    // Remove discount after use
    $pdo->prepare("DELETE FROM discountoffer WHERE DUserID=?")->execute([$uid]);

    // --- 🔽 New: Decrease stock quantities ---
    $cartItemsStmt = $pdo->prepare("
        SELECT c.CartID, c.Quantity, p.ProductID, p.StockQuantity
        FROM cart c
        JOIN buys b ON c.CartID = b.CartID
        JOIN product p ON b.ProductID = p.ProductID
        WHERE c.BUserID = ?
    ");
    $cartItemsStmt->execute([$uid]);
    $cartItems = $cartItemsStmt->fetchAll();

    foreach ($cartItems as $item) {
        $newStock = $item['StockQuantity'] - $item['Quantity'];
        if ($newStock < 0) {
            throw new Exception("❌ Insufficient stock for ProductID " . $item['ProductID']);
        }
        $pdo->prepare("UPDATE product SET StockQuantity=? WHERE ProductID=?")
            ->execute([$newStock, $item['ProductID']]);
    }

    // Move cart items into places table & clear cart
    foreach ($cartItems as $c) {
        $b = $pdo->prepare("SELECT ProductID FROM buys WHERE CartID=?");
        $b->execute([$c['CartID']]);
        $bp = $b->fetch();

        if ($bp) {
            $pdo->prepare("INSERT IGNORE INTO places (UserID, ProductID, OrderID) VALUES (?, ?, ?)")
                ->execute([$uid, $bp['ProductID'], $orderID]);
        }

        $pdo->prepare("DELETE FROM buys WHERE CartID=?")->execute([$c['CartID']]);
        $pdo->prepare("DELETE FROM cart WHERE CartID=?")->execute([$c['CartID']]);
    }

    // Record payment method
    $pdo->prepare("INSERT INTO payment_method (PORDERID) VALUES (?)")->execute([$orderID]);
    $transactionID = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO gateway_type (Gateway_Type, TransactionID) VALUES (?, ?)")
        ->execute([$method, $transactionID]);

    // Update user total_spent and loyalty level
    $pdo->prepare("UPDATE `user` SET total_spent = total_spent + ? WHERE UserID=?")
        ->execute([$finalTotal, $uid]);
    $ts = $pdo->prepare("SELECT total_spent FROM `user` WHERE UserID=?");
    $ts->execute([$uid]);
 

} catch (Throwable $e) {
    die("❌ Error processing payment: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Placed</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
        font-family: 'Fredoka', sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container {
        text-align: center;
        background: #fff;
        padding: 40px 30px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .container h2 {
        color: #4CAF50;
        margin-bottom: 20px;
        font-weight: 500;
    }
    .container p {
        color: #333;
        margin: 8px 0;
        font-size: 16px;
    }
    .container a {
        display: inline-block;
        margin: 10px 5px 0 5px;
        padding: 10px 18px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.2s;
    }
    .container a:hover {
        background-color: #45a049;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🎉 Order Successfully Placed!</h2>
    <p>Your order ID: <?= htmlspecialchars($orderID) ?></p>
    <p>Total Paid: <?= number_format($finalTotal, 2) ?></p>
    <p>Payment Method: <?= htmlspecialchars($method) ?></p>
    <p><a href="order_memo.php?order=<?= $orderID ?>">View Memo</a></p>
    <p><a href="index.php">Back to Home</a></p>
  </div>
</body>
</html>
