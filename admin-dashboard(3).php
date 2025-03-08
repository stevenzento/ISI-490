<?php
session_start();
include 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify that the user is an admin before showing incidents
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check if user is an admin
$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if (!$is_admin) {
    echo "You don't have permission to view this page.";
    exit;
}

// Fetch incidents from the database
$result = $conn->query("SELECT * FROM incidents");
?>

Now your table code can use `$result` without errors:

```php
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
