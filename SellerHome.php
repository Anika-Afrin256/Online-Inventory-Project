<?php 
require 'config.php'; 

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Home - Online Inventory</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      font-family: "Fredoka", sans-serif;
      background: linear-gradient(135deg, #f0f4ff, #e6f7f1);
      color: #333;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      padding: 20px;
    }
    header {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 15px 0;
      margin-bottom: 30px;
    }
    header a {
      margin-left: 15px;
      text-decoration: none;
      color: #4CAF50;
      font-weight: 500;
      transition: color 0.3s;
    }
    header a:hover { color: #388e3c; }
    main section {
      background: #fff;
      padding: 25px;
      margin-bottom: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    h1, h2, h3 {
      font-weight: 500;
      margin-bottom: 15px;
      color: #222;
    }
    p, li {
      line-height: 1.6;
      font-size: 15px;
    }
    ul { padding-left: 20px; }
    select, button {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      margin-top: 10px;
    }
    button {
      background: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
      margin-left: 10px;
      transition: 0.3s;
    }
    button:hover { background: #45a049; }
    a {
      color: #4CAF50;
      text-decoration: none;
      font-weight: 500;
    }
    a:hover { text-decoration: underline; }

    /* Recent orders styling */
    .orders ul {
      list-style-type: none;
      padding: 0;
    }
    .orders li {
      padding: 8px 12px;
      border-bottom: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .orders li:last-child { border-bottom: none; }
    .orders a {
      background: #4CAF50;
      color: #fff;
      padding: 5px 12px;
      border-radius: 5px;
      font-size: 13px;
    }
    .orders a:hover { background: #45a049; }
  </style>
</head>
<body>
<div class="container">
<header>
  <?php if(!empty($_SESSION['user_id'])): ?>
    Hello, <?=htmlspecialchars($_SESSION['user_name'] ?? 'User')?> | 
    <a href="logout.php">Logout</a> | <a href="cart.php">Cart</a>
  <?php else: ?>
    <a href="login.php">Login</a> | <a href="register.php">Register</a>
  <?php endif; ?>
</header>

<main>

<section>
  <h1>🌟 Welcome to Online Inventory & Order Management 🌟</h1>
  <p>
    We are <b>Mohammad Asif Al Mahfuz, Anika Afrin, and Mehrun-Nisa</b>, creators of this all-in-one Online Inventory & Order Management System designed to make your shopping journey simpler, faster, and more rewarding.
  </p>
  <ul>
    <li>👉 <b>Exclusive Smart Discounts</b> – unlock unique offers with every purchase.</li>
    <li>👉 <b>Seamless Experience</b> – speed, clarity, convenience.</li>
    <li>👉 <b>Transparent Management</b> – product availability and order tracking.</li>
  </ul>
  <p>💡 Start exploring today and experience the future of shopping.</p>
</section>

<section>
  <h2>Shop by Category</h2>
  <form action="products.php" method="get">
    <select name="cat">
      <option value="">All</option>
      <?php
      $cats = $pdo->query("SELECT DISTINCT Catagory FROM catagory")->fetchAll();
      foreach($cats as $c) echo "<option value='".htmlspecialchars($c['Catagory'])."'>".htmlspecialchars($c['Catagory'])."</option>";
      ?>
    </select>
    <button>Go</button>
  </form>
</section>

<section>
  <h2>Seller Option</h2>
  <p><a href="selle_upload.php">Click here to add products</a></p>
</section>


</main>
</div>
</body>
</html>