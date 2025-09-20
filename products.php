<?php 
require 'config.php';
$cat = $_GET['cat'] ?? '';
if ($cat) {
  $stmt = $pdo->prepare("SELECT p.* FROM product p 
                         JOIN catagory c ON p.ProductID=c.ProductID 
                         WHERE c.Catagory=?");
  $stmt->execute([$cat]);
} else {
  $stmt = $pdo->query("SELECT * FROM product");
}
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Fredoka', sans-serif; background-color: #f9f9f9; margin:0; padding:0; }
nav { display: flex; justify-content: space-between; align-items: center; background-color: #4CAF50; padding: 10px 20px; }
nav a { color: white; text-decoration: none; margin: 0 10px; }
nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
nav ul li { margin: 0 10px; }

.container { max-width: 1200px; margin: auto; padding: 50px 20px; min-height: 80vh; }
.container h2 { text-align: center; margin-bottom: 30px; font-weight: 400; color: #333; }

.products_grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
.product-card { width: 220px; background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; transition: transform 0.2s, box-shadow 0.2s; }
.product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
.product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; }
.product-card h4 { margin: 10px 0 5px; color: #333; font-weight: 500; }
.product-card p { margin: 5px 0; color: #555; font-size: 14px; }
.product-card a { display: inline-block; margin-top: 10px; padding: 8px 12px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; transition: background 0.2s; }
.product-card a:hover { background-color: #45a049; }

.no-products { text-align: center; color: #555; margin-top: 20px; font-size: 16px; }
</style>
</head>
<body>
<header>
<nav>
    <div class="nav_logo"><h1><a href="index.php">Online Inventory</a></h1></div>
    <ul class="nav_link">
        <li><a href="index.php">Home</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="products.php">Products</a></li>
        <!-- <li><a href="seediscount.php">Discounts</a></li>    go through -->
    </ul>
</nav>
</header>
<main class="container">
  <h2>Products <?= $cat ? "in ".htmlspecialchars($cat):'' ?></h2>

  <?php if (!$rows): ?>
    <p class="no-products">No products available.</p>
  <?php endif; ?>

  <div class="products_grid">
  <?php foreach($rows as $r): ?>
    <div class="product-card">
      <img src="<?=htmlspecialchars($r['ImagePath'])?>" alt="Product">
      <h4><?=htmlspecialchars($r['Name'])?></h4>
      <p>Brand: <?=htmlspecialchars($r['Brand'])?></p>
      <p>Price: <?=htmlspecialchars($r['Price'])?></p>
      <p>Stock: <?=htmlspecialchars($r['StockQuantity'])?></p>
      <a href="product_detail.php?id=<?=$r['ProductID']?>">View / Add to Cart</a>
    </div>
  <?php endforeach; ?>
  </div>
</main>
</body>
</html>
