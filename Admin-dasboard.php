<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// Admin credentials
$admin_username = 'Cawa';
$admin_password = 'Review101';

// Handle Admin Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin_login"])) {
    $entered_username = trim($_POST["admin_username"]);
    $entered_password = $_POST["admin_password"];

    if ($entered_username === $admin_username && $entered_password === $admin_password) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $message = "❌ Invalid admin credentials!";
    }
}

// Check Admin Session
if (!isset($_SESSION["admin_logged_in"])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CAWA</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0069d9; }
        .message { margin: 10px 0; color: red; }
    </style>
</head>
<body>
    <h1>🔒 Admin Login</h1>
    <div class="container">
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST">
            <input type="text" name="admin_username" placeholder="Username" required>
            <input type="password" name="admin_password" placeholder="Password" required>
            <button type="submit" name="admin_login">Login</button>
        </form>
    </div>
</body>
</html>
<?php
exit;
}

// Handle Incident Status Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $incident_id = $_POST["incident_id"];
    $new_status = $_POST["status"];

    $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $incident_id);
    
    if ($stmt->execute()) {
        $message = "✅ Status updated successfully!";
    } else {
        $message = "❌ Error updating status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch incident data
$result = $conn->query("SELECT * FROM incidents ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Review Incidents</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; padding: 8px; border-radius: 5px; }
        button:hover { background-color: #218838; }
        .logout { background-color: red; color: white; padding: 8px 15px; margin-bottom: 20px; border-radius: 5px; border: none; cursor: pointer; }
        .logout:hover { background-color: #cc0000; }
        .message { color: green; }
    </style>
</head>
<body>
    <h1>Admin - Review Incident Reports</h1>
    <a href="admin_logout.php"><button class="logout">Admin Logout</button></a>

    <div class="container">
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Description</th>
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
                    <td><?= htmlspecialchars($row["status"]) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="incident_id" value="<?= $row["id"] ?>">
                            <select name="status">
                                <option value="Pending" <?= ($row["status"] == "Pending") ? "selected" : "" ?>>Pending</option>
                                <option value="Approved" <?= ($row["status"] == "Approved") ? "selected" : "" ?>>Approved</option>
                                <option value="Not Approved" <?= ($row["status"] == "Not Approved") ? "selected" : "" ?>>Not Approved</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <a href="admin_logout.php"><button class="logout">Admin Logout</button></a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
