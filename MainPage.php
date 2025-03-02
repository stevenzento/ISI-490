<?php
session_start();
include 'db_connect.php';

// Handle Login
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        $message = "✅ Logged in successfully!";
    } else {
        $message = "❌ Invalid username or password!";
    }
    $stmt->close();
}

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        $message = "✅ Registered successfully! Please log in.";
    } else {
        $message = "❌ Registration failed: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 CAWA - Login & Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin: 10px 0;
            color: red;
        }
        .welcome {
            color: #28a745;
        }
    </style>
</head>
<body>
    <h1>🔥 CAWA - Community Alert Web App</h1>

    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION["user_id"])): ?>
        <!-- Registration Form -->
        <div class="container">
            <h2>Register</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
            </form>
        </div>

        <!-- Login Form -->
        <div class="container">
            <h2>Login</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Logged In State -->
        <div class="container">
            <h2 class="welcome">Welcome, <?= $_SESSION["username"] ?>!</h2>
            <p>You are now logged in.</p>
            <a href="logout.php"><button>Logout</button></a>
        </div>
    <?php endif; ?>
</body>
</html>
