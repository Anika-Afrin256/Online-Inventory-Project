<?php
require 'config.php';
require_once __DIR__ . '/discount_engine.php';

if (empty($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
$uid = (int)$_SESSION['user_id'];

/* ===========================
   Remove item from cart (POST)
   =========================== */
// NEW handler — use this instead of the old one
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_cart_id'])) {
    $cartId = (int)$_POST['remove_cart_id'];

    // Ensure this cart row belongs to the logged-in user
    $own = $pdo->prepare("SELECT 1 FROM cart WHERE CartID = ? AND BUserID = ?");
    $own->execute([$cartId, $uid]);
    if (!$own->fetchColumn()) {
        header("Location: cart.php");
        exit;
    }

    $pdo->beginTransaction();
    try {
        // Delete children first to satisfy FK
        $stmt1 = $pdo->prepare("DELETE FROM buys WHERE CartID = ?");
        $stmt1->execute([$cartId]);

        // Then delete the parent row
        $stmt2 = $pdo->prepare("DELETE FROM cart WHERE CartID = ? AND BUserID = ?");
        $stmt2->execute([$cartId, $uid]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        // Optional: error_log($e->getMessage());
    }

    header("Location: cart.php");
    exit;
}


/* =======================================
   Recompute discounts to keep UI accurate
   ======================================= */
try { 
    apply_discounts_to_cart_pdo($pdo, $uid); 
} catch (Throwable $e) { 
    // Optional: error_log($e->getMessage());
}

/* ===========================
   Load cart items for display
   =========================== */
$stmt = $pdo->prepare("
    SELECT c.CartID, c.Quantity, c.Total, c.Discount_Applied,
           p.Name, p.Price, p.ProductID, COALESCE(p.ImagePath, '') AS ImagePath
    FROM cart c
    JOIN buys b ON c.CartID = b.CartID
    JOIN product p ON p.ProductID = b.ProductID
    WHERE c.BUserID = ?
");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

$subtotal = 0.0; 
$discount_total = 0.0;
foreach ($items as $it) { 
    $subtotal += (float)$it['Total']; 
    $discount_total += (float)$it['Discount_Applied']; 
}
$final_total = max(0.0, $subtotal - $discount_total);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />
<title>Your Cart - Online Inventory</title>
<style>
    body { font-family: 'Fredoka', sans-serif; background:#f9f9f9; margin:0; padding:0; }
    nav { display:flex; justify-content:space-between; align-items:center; background:#4CAF50; padding:10px 20px; }
    nav a { color:#fff; text-decoration:none; margin:0 10px; }
    nav ul { list-style:none; display:flex; margin:0; padding:0; }
    nav ul li { margin:0 10px; }
    main { padding:50px 20px; min-height:80vh; }
    .cart_box { max-width:900px; margin:auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
    .cart_box h2 { text-align:center; margin-bottom:20px; font-weight:500; color:#333; }
    .cart_item { 
        display:grid; 
        grid-template-columns: 64px 1fr auto auto auto; 
        gap:12px; 
        align-items:center; 
        padding:10px 0; 
        border-bottom:1px solid #eee; 
        color:#333; 
    }
    .cart_item img { width:64px; height:64px; object-fit:cover; border-radius:8px; }
    .cart_totals { text-align:right; font-weight:bold; margin-top:15px; }
    .checkout_btn { display:block; margin:20px auto 0; padding:12px 18px; background:#4CAF50; color:#fff; border-radius:8px; text-align:center; text-decoration:none; width:50%; }
    .checkout_btn:hover { background:#45a049; }
    .muted { color:#666; font-weight: normal; }
    .remove_btn { background:#e74c3c; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; }
    .remove_btn:hover { opacity:.9; }
</style>
</head>
<body>
<header>
  <nav>
    <div class="nav_logo"><h1><a href="index.php">Online Inventory</a></h1></div>
    <ul class="nav_link">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Products</a></li>
    </ul>
  </nav>
</header>
<main>
  <div class="cart_box">
    <h2>Your Cart</h2>
    <?php if ($items): ?>
      <?php foreach ($items as $it):
         $line_final = max(0.0, (float)$it['Total'] - (float)$it['Discount_Applied']); ?>
        <div class="cart_item">
          <?php if (!empty($it['ImagePath'])): ?>
            <img src="<?=htmlspecialchars($it['ImagePath'])?>" alt="<?=htmlspecialchars($it['Name'])?>" />
          <?php else: ?>
            <div></div>
          <?php endif; ?>

          <div>
            <?=htmlspecialchars($it['Name'])?><br>
            <span class="muted">Qty: <?=htmlspecialchars($it['Quantity'])?></span>
          </div>

          <div class="muted">Subtotal: <?=number_format((float)$it['Total'],2)?></div>
          
          <!-- Remove button -->
          <form method="post" action="cart.php" style="display:inline;">
            <input type="hidden" name="remove_cart_id" value="<?= (int)$it['CartID'] ?>">
            <button type="submit" class="remove_btn">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>

      <div class="cart_totals">
        Subtotal: <?=number_format($subtotal,2)?> &nbsp; | &nbsp;
        Discounts: -<?=number_format($discount_total,2)?> &nbsp; | &nbsp;
        <span style="font-size:1.1em">Total: <?=number_format($final_total,2)?></span>
      </div>
      <a href="checkout.php" class="checkout_btn">Proceed to Checkout</a>
    <?php else: ?>
      <p style="text-align:center;">Your cart is empty.</p>
    <?php endif; ?>
    <p>Want to add shoes? <a href="products.php">Add product to cart</a></p>
  </div>
</main>
</body>
</html>