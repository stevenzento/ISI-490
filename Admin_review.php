<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// Handle Admin Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin_review"])) {
    $admin_password = trim($_POST["admin_password"]);

    if ($admin_password === "Review101") {
        $_SESSION["is_admin"] = true;
    } else {
        $message = "❌ Incorrect admin password!";
    }
}

// Ensure only the admin can access this page
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Review - CAWA</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin: 50px; background-color: #f4f4f4; }
            .container { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.1); }
            input, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
            button { background-color: #007bff; color: white; border: none; cursor: pointer; }
            button:hover { background-color: #0056b3; }
        </style>
    </head>
    <body>
        <h1>Admin Review Login</h1>
        <div class="container">
            <form method="POST">
                <input type="password" name="admin_password" placeholder="Enter Admin Review Password" required>
                <button type="submit" name="admin_review">Login</button>
            </form>
            <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
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
        $message = "✅ Incident status updated successfully!";
    } else {
        $message = "❌ Failed to update status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch All Incidents for Admin Review
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
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        select, button { padding: 5px; margin: 5px; }
        button { background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>
    <h1>Admin - Review Incident Reports</h1>

    <div class="container">
        <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

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
    </div>
</body>
</html>
