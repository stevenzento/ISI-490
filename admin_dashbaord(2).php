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
