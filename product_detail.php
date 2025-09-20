<?php 
require 'config.php';


$id = (int)($_GET['id'] ?? 0);
$p = $pdo->prepare("SELECT * FROM product WHERE ProductID=?");
$p->execute([$id]); 
$prod = $p->fetch();

if (!$prod) {
    echo "<h2>Product not found.</h2>";
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (empty($_SESSION['user_id'])) { 
        header('Location: login.php'); exit;
    }
    $q = (int)$_POST['qty'];
    if ($q < 1) $q = 1;

    $total = $prod['Price'] * $q;

    $ins = $pdo->prepare("INSERT INTO cart (Quantity, Discount_Applied, Total, BUserID) VALUES (?,?,?,?)");
    $ins->execute([$q, 0, $total, $_SESSION['user_id']]);
    $cartID = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO buys (BUserID, CartID, ProductID) VALUES (?,?,?)")
       ->execute([$_SESSION['user_id'],$cartID,$id]);

    header('Location: cart.php'); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=htmlspecialchars($prod['Name'])?></title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />
<style>
    body { font-family: 'Fredoka', sans-serif; background-color: #2e7d32; margin: 0; padding: 0; }
    nav { display: flex; justify-content: space-between; align-items: center; background-color: #1b5e20; padding: 10px 20px; }
    nav a { color: white; text-decoration: none; margin: 0 10px; }
    nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
    nav ul li { margin: 0 10px; }
    .container { max-width: 600px; margin: 40px auto; padding: 30px; background-color: rgba(0,0,0,0.3); border-radius: 10px; color: white; text-align: center; }
    img { border-radius: 10px; max-width: 100%; height: auto; margin-bottom: 20px; }
    h2 { margin-bottom: 20px; }
    p { margin: 10px 0; }
    input[type="number"] { width: 80px; padding: 8px; border-radius: 5px; border: none; text-align: center; }
    button { padding: 12px 20px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; font-size: 16px; cursor: pointer; }
    button:hover { background-color: #66bb6a; }
    a { color: #fff; text-decoration: underline; display: inline-block; margin-top: 15px; }
</style>
</head>
<body>
<header>
    <nav>
        <div class="nav_logo"><h1><a href="index.php">Online Inventory</a></h1></div>
        <ul class="nav_link">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <!-- <li><a href="seediscount.php">Discounts</a></li>   //go through // -->
        </ul>
    </nav>
</header>
<main>
<div class="container">
    <h2><?=htmlspecialchars($prod['Name'])?></h2>
    <?php if ($prod['ImagePath']): ?>
        <img src="<?=htmlspecialchars($prod['ImagePath'])?>" alt="<?=htmlspecialchars($prod['Name'])?>">
    <?php endif; ?>
    <p>Price: <?=htmlspecialchars($prod['Price'])?></p>
    <p>Stock: <?=htmlspecialchars($prod['StockQuantity'])?></p>

    <form method="post">
        <label>Quantity:</label>
        <input name="qty" type="number" value="1" min="1" max="<?=htmlspecialchars($prod['StockQuantity'])?>">
        <br><br>
        <button type="submit">Add to Cart</button>
    </form>
    <a href="products.php">⬅ Back to Products</a>
</div>
</main>
</body>
</html>
