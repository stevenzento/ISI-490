<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

// Handle User Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone_number = trim($_POST["phone_number"]);

    $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $name, $email, $password, $phone_number);

    if ($stmt->execute()) {
        $message = "✅ Registered successfully! Please log in.";
    } else {
        $message = "❌ Registration failed: " . $conn->error;
    }
    $stmt->close();
}

// Handle User Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password, name FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $name);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["name"] = $name;
            $message = "✅ Logged in successfully!";
            header("Location: mainpage.php");
            exit;
        } else {
            $message = "❌ Invalid username or password!";
        }
    } else {
        $message = "❌ Invalid username or password!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 CAWA - Community Alert Web App</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        .message { margin: 10px 0; color: red; }
        .logout { background-color: red; }
        .admin-btn { width: 150px; padding: 8px; margin-top: 20px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .admin-btn:hover { background-color: #0069d9; }
    </style>
</head>
<body>
    <h1>🔥 CAWA - Community Alert Web App</h1>

    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION["user_id"])): ?>
        <!-- User Registration -->
        <div class="container">
            <h2>Register</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <button type="submit" name="register">Register</button>
            </form>
        </div>

        <!-- User Login -->
        <div class="container">
            <h2>Login</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Logged In User -->
        <div class="container">
            <h2 class="welcome">Welcome, <?= $_SESSION["name"] ?>!</h2>
            <p>You are now logged in.</p>
            <a href="user_profile.php"><button>Manage Profile</button></a>
            <a href="logout.php"><button class="logout">Logout</button></a>
        </div>
    <?php endif; ?>

    <div>
        <a href="admin_dashboard.php"><button class="admin-btn">Admin Login</button></a>
    </div>
</body>
</html>
