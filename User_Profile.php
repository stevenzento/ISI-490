<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: MainPage.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Fetch user details
$stmt = $conn->prepare("SELECT username, name, email, phone_number FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($username, $name, $email, $phone_number);
$stmt->fetch();
$stmt->close();

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $new_username = trim($_POST["username"]);
    $new_name = trim($_POST["name"]);
    $new_email = trim($_POST["email"]);
    $new_phone = trim($_POST["phone_number"]);

    $stmt = $conn->prepare("UPDATE users SET username=?, name=?, email=?, phone_number=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $new_username, $new_name, $new_email, $new_phone, $user_id);

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
        $_SESSION["name"] = $new_name; // Update session name
    } else {
        $message = "❌ Error updating profile: " . $conn->error;
    }
    $stmt->close();
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $current_password = $_POST["current_password"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (password_verify($current_password, $hashed_password)) {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->bind_param("si", $new_password, $user_id);
        
        if ($stmt->execute()) {
            $message = "✅ Password changed successfully!";
        } else {
            $message = "❌ Error updating password.";
        }
    } else {
        $message = "❌ Current password is incorrect.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - CAWA</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        .message { margin: 10px 0; color: red; }
        .logout { background-color: red; }
    </style>
</head>
<body>
    <h1>👤 User Profile</h1>

    <div class="container">
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <!-- Profile Update Form -->
        <h2>Update Profile</h2>
        <form method="POST">
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($phone_number) ?>" required>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- Password Change Form -->
        <h2>Change Password</h2>
        <form method="POST">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="change_password">Change Password</button>
        </form>

        <!-- Logout Button -->
        <a href="logout.php"><button class="logout">Logout</button></a>
    </div>
</body>
</html>
