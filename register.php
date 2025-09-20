<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $name = $_POST['name'] ?? '';
  $contact = $_POST['contact'] ?? '';
  $street = $_POST['street'] ?? '';
  $city = $_POST['city'] ?? '';
  $building_no = $_POST['building_no'] ?? '';
  $user_type = $_POST['user_type'] ?? '';

  if (empty($username) || empty($email) || empty($password)) {
    $error = "Please fill required fields.";
  } else {
    // check duplicates
    $stmt = $pdo->prepare("SELECT UserID FROM `user` WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
      $error = "Username or email already taken.";
    } else {
      // hash password
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $pdo->prepare("INSERT INTO `user` 
        (username, Password, Name, Contact, email, Street, City, Building_No, User_Type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $ins->execute([$username, $hash, $name, $contact, $email, $street, $city, $building_no, $user_type]);

      // set session variables
      $newUserId = $pdo->lastInsertId();
      $_SESSION['user_id'] = $newUserId;
      $_SESSION['user_name'] = $name;
      $_SESSION['user_type'] = $user_type;

      // redirect based on type
      if ($user_type === 'Buyer') {
          header('Location: BuyerHome.php');
      } elseif ($user_type === 'Seller') {
          header('Location: SellerHome.php');
      } else {
          header('Location: index.php');
      }
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - Online Inventory</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet" />
  <style>
    main { padding: 50px 20px; background-color: #f9f9f9; min-height: 80vh; }
    .register_box { max-width: 500px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .register_box h2 { text-align: center; margin-bottom: 20px; font-weight: 400; color: #333; }
    .register_box input, .register_box select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    .register_box button { background-color: #4CAF50; color: white; border: none; cursor: pointer; transition: 0.3s; padding: 10px; width: 100%; border-radius: 5px; }
    .register_box button:hover { background-color: #45a049; }
    .error_message { color: red; text-align: center; margin-bottom: 15px; }
  </style>
</head>
<body>
<header>
  <nav>
    <div class="nav_logo">
      <h1><a href="index.php">Online Inventory</a></h1>
    </div>
    <ul class="nav_link">
      <li><a href="index.php">Home</a></li>
    </ul>
  </nav>
</header>
<main>
  <div class="register_box">
    <h2>Register</h2>
    <?php if(!empty($error)) echo "<p class='error_message'>$error</p>"; ?>
    <form method="post">
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="text" name="name" placeholder="Full Name">
      <input type="text" name="contact" placeholder="Contact">
      <input type="text" name="street" placeholder="Street">
      <input type="text" name="city" placeholder="City">
      <input type="number" name="building_no" placeholder="Building No">
      <select name="user_type" required>
        <option value="">Select User Type</option>
        <option value="Buyer">Buyer</option>
        <option value="Seller">Seller</option>
      </select>
      <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top:10px;">Already have an account? <a href="login.php">Login</a></p>
  </div>
</main>
</body>
</html>



