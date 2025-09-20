<?php
require 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid   = (int)$_SESSION['user_id'];
$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = $_POST['price'] ?? '';
    $qty   = $_POST['qty'] ?? '';
    $cat   = trim($_POST['cat'] ?? '');

    if ($name === '' || $brand === '' || $price === '' || $qty === '') {
        $error = 'Name, Brand, Price, and Stock Quantity are required.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Price must be a non-negative number.';
    } elseif (!ctype_digit((string)$qty) || (int)$qty < 0) {
        $error = 'Stock Quantity must be a non-negative integer.';
    }

    $dbPath = null;
    if (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($_FILES['image']['tmp_name']);
            $ok    = in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'], true);

            if (!$ok) {
                $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
            } else {
                $extMap = [
                    'image/jpeg' => '.jpg',
                    'image/png'  => '.png',
                    'image/gif'  => '.gif',
                    'image/webp' => '.webp',
                ];
                $ext   = $extMap[$mime] ?? '';
                $dir   = __DIR__ . '/uploads';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                $basename = bin2hex(random_bytes(8)) . $ext;
                $fullpath = $dir . '/' . $basename;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $fullpath)) {
                    $error = 'Failed to move uploaded file.';
                } else {
                    $dbPath = 'uploads/' . $basename;
                }
            }
        }
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            $ins = $pdo->prepare("
                INSERT INTO product (Name, Brand, StockQuantity, Price, Review, SUserID, DofferID, ImagePath)
                VALUES (:name, :brand, :qty, :price, :review, :suid, NULL, :img)
            ");
            $ins->bindValue(':name',   $name);
            $ins->bindValue(':brand',  $brand);
            $ins->bindValue(':qty',    (int)$qty, PDO::PARAM_INT);
            $ins->bindValue(':price',  (int)$price, PDO::PARAM_INT);
            $ins->bindValue(':review', '');
            $ins->bindValue(':suid',   $uid, PDO::PARAM_INT);
            $ins->bindValue(':img',    $dbPath);

            $ins->execute();
            $productId = (int)$pdo->lastInsertId();

            if ($cat !== '') {
                $catIns = $pdo->prepare("INSERT INTO catagory (Catagory, ProductID) VALUES (?, ?)");
                $catIns->execute([$cat, $productId]);
            }

            $pdo->commit();
            $msg = 'Product uploaded successfully.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Could not save product. ' . ($e instanceof PDOException ? 'Database constraint failed.' : 'Unexpected error.');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Upload</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300.700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Fredoka', sans-serif; background-color: #2e7d32; margin: 0; padding: 0; }
nav { display: flex; justify-content: space-between; align-items: center; background-color: #1b5e20; padding: 10px 20px; }
nav a { color: white; text-decoration: none; margin: 0 10px; }
nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
nav ul li { margin: 0 10px; }
.container { max-width: 700px; margin: 40px auto; padding: 30px; background-color: rgba(0,0,0,0.3); border-radius: 10px; color: white; }
h2 { text-align: center; margin-bottom: 20px; }
form p { margin: 15px 0; }
input[type="text"], input[type="number"], input[type="file"], select { width: 100%; padding: 10px; border-radius: 5px; border: none; }
button { width: 100%; padding: 12px; border: none; border-radius: 5px; background-color: #4CAF50; color: white; font-size: 16px; cursor: pointer; }
button:hover { background-color: #66bb6a; }
a { color: #fff; text-decoration: underline; }
p.success { color: lightgreen; }
p.error { color: #ffcccb; }
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
<div class="container">
    <h2>Add a New Product</h2>
    <?php if($msg)   echo "<p class='success'>".htmlspecialchars($msg)."</p>"; ?>
    <?php if($error) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>

    <form method="post" enctype="multipart/form-data">
        <p><input name="name"  placeholder="Product Name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></p>
        <p><input name="brand" placeholder="Brand" value="<?=htmlspecialchars($_POST['brand'] ?? '')?>"></p>
        <p><input name="price" type="number" placeholder="Price" value="<?=htmlspecialchars($_POST['price'] ?? '')?>"></p>
        <p><input name="qty"   type="number" placeholder="Stock Quantity" value="<?=htmlspecialchars($_POST['qty'] ?? '')?>"></p>
        <p><input name="cat"   placeholder="Category" value="<?=htmlspecialchars($_POST['cat'] ?? '')?>"></p>
        <p><input type="file" name="image" accept="image/*"></p>
        <button type="submit">Upload Product</button>
    </form>

    <p><a href="index.php">⬅ Back to Home</a></p>
</div>
</main>
</body>
</html>
