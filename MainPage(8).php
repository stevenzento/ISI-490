<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

// Handle User Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password, name, is_admin FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $name, $is_admin);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["name"] = $name;
            $_SESSION["is_admin"] = $is_admin;
            $message = "✅ Logged in successfully!";
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
        .container { max-width: 700px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
        input, button, select { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
        .message { margin: 10px 0; color: red; }
        .logout { background-color: red; }
    </style>
</head>
<body>
    <h1>🔥 CAWA - Community Alert Web App</h1>

    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION["user_id"])): ?>
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
        <?php if ($_SESSION["is_admin"] == 1): ?>
            <!-- Admin View -->
            <div class="container">
                <h2 class="welcome">Admin Panel - Welcome, <?= $_SESSION["name"] ?>!</h2>
                <?php
                $result = $conn->query("SELECT * FROM incidents");
                ?>
                <table border="1">
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Description</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row["id"] ?></td>
                            <td><?= htmlspecialchars($row["location"]) ?></td>
                            <td><?= htmlspecialchars($row["type"]) ?></td>
                            <td><?= htmlspecialchars($row["priority"]) ?></td>
                            <td><?= htmlspecialchars($row["description"]) ?></td>
                            <td><?= $row["latitude"] ?></td>
                            <td><?= $row["longitude"] ?></td>
                            <td>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="incident_id" value="<?= $row["id"] ?>">
                                    <select name="status">
                                        <option <?= ($row["status"] == "Pending") ? "selected" : "" ?>>Pending</option>
                                        <option <?= ($row["status"] == "Approved") ? "selected" : "" ?>>Approved</option>
                                        <option <?= ($row["status"] == "Not Approved") ? "selected" : "" ?>>Not Approved</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <a href="logout.php"><button class="logout">Logout</button></a>
            </div>

        <?php else: ?>
            <!-- Regular User View -->
            <div class="container">
                <h2 class="welcome">Welcome, <?= $_SESSION["name"] ?>!</h2>
                <p>You are logged in as a user.</p>
                <a href="user_profile.php"><button>Manage Profile</button></a>
                <a href="logout.php"><button class="logout">Logout</button></a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
