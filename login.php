<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = $_POST['username']; 
  $p = $_POST['password'];

  // fetch User_Type also
  $stmt = $pdo->prepare("SELECT UserID, Password, Name, User_Type FROM `user` WHERE username=? OR email=? LIMIT 1");
  $stmt->execute([$u,$u]);
  $row = $stmt->fetch();

  if ($row && password_verify($p, $row['Password'])) {
    $_SESSION['user_id'] = $row['UserID'];
    $_SESSION['user_name'] = $row['Name'];
    $_SESSION['user_type'] = $row['User_Type'];

    // redirect based on user type
    if ($row['User_Type'] === 'Buyer') {
        header('Location: BuyerHome.php');
    } elseif ($row['User_Type'] === 'Seller') {
        header('Location: SellerHome.php');
    } else {
        // fallback if somehow another type is found
        header('Location: index.php');
    }
    exit;
  } else {
    $error = "Invalid credentials";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap"
    rel="stylesheet"
  />
</head>
<body>
  <header>
    <nav>
      <div class="nav_logo">
        <h1><a href="index.php">Online Inventory</a></h1>
      </div>
      <ul class="nav_link">
        <li><a href="index.php">Home</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="products.php">Products</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <section class="login">
      <div class="login_box">
        <h1>Login</h1>
        <?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form class="login_form" method="post">
          <input type="text" name="username" placeholder="Username or Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <input type="submit" value="Login" />
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
      </div>
    </section>
  </main>
</body>
</html>