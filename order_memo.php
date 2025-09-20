<?php require 'config.php';
$order = (int)($_GET['order'] ?? 0);

//order er details pabo order table theke
$o = $pdo->prepare("SELECT * FROM `order` WHERE OrderID=?"); 
$o->execute([$order]); 
$ord = $o->fetch();
if (!$ord) exit("Invalid order");

//places table e order r product er modhhe connection ase okhan theke item er naam pabo 
$items = $pdo->prepare("SELECT p.Name FROM places pl JOIN product p ON pl.ProductID=p.ProductID WHERE pl.OrderID=?");
$items->execute([$order]); 
$it = $items->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Purchase Memo — Order #<?=$ord['OrderID']?></title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Fredoka', sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
nav { display: flex; justify-content: space-between; align-items: center; background-color: #4CAF50; padding: 10px 20px; }
nav a { color: white; text-decoration: none; margin: 0 10px; }
nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
nav ul li { margin: 0 10px; }

.main_box { padding: 50px 20px; min-height: 80vh; }
.memo_box { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.memo_box h2 { text-align: center; margin-bottom: 20px; font-weight: 400; color: #333; }
.memo_box p { margin: 10px 0; color: #333; }
.memo_box ul { list-style: none; padding-left: 0; }
.memo_box ul li { padding: 8px 0; border-bottom: 1px solid #ddd; color: #333; }
.memo_box .totals { margin-top: 20px; }
.memo_box button { display: block; margin: 20px auto 0; padding: 12px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
.memo_box button:hover { background-color: #45a049; }
</style>
</head>
<body>
<header>
<nav>
    <div class="nav_logo"><h1><a href="index.php">Online Inventory</a></h1></div>
    <ul class="nav_link">
        <li><a href="index.php">Home</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
</header>
<main class="main_box">
<div class="memo_box">
<h2>Purchase Memo — Order #<?=$ord['OrderID']?></h2>
<p>Order date: <?=$ord['Order_date']?> | Status: <?=$ord['Status']?></p>
<p>Shipping address: <?=$ord['Shipping_Address']?></p>

<p>Items:</p>
<ul>
<?php foreach($it as $i) echo "<li>".htmlspecialchars($i['Name'])."</li>"; ?>
</ul>

<div class="totals">
    <p><strong>Total: <?=$ord['TotalAmount']?></strong></p>
    <p>Payment method: <?=$ord['PaymentMethod']?></p>
</div>

<button onclick="window.print()">Print Memo</button>
</div>
</main>
</body>
</html>



