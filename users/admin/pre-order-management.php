<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $idToUpdate = intval($_POST['id']);
    $newStatus = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pre_orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $newStatus, $idToUpdate);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Pre-order status updated.";
    header("Location: pre-order-management.php");
    exit();
}

// Handle delete
if (isset($_POST['delete_pre_order'])) {
    $idToDelete = intval($_POST['id']);

    // Delete related pre_order_items first
    $stmt = $conn->prepare("DELETE FROM pre_order_items WHERE pre_order_id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    // Delete the pre_order
    $stmt = $conn->prepare("DELETE FROM pre_orders WHERE id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Pre-order deleted successfully.";
    header("Location: pre-order-management.php");
    exit();
}

// Fetch all pre-orders
$query = "
    SELECT po.id, po.order_date, po.status, u.username, r.reservation_date
    FROM pre_orders po
    JOIN user u ON po.user_id = u.id
    LEFT JOIN reservations r ON po.reservation_id = r.id
    ORDER BY po.id DESC
";
$result = $conn->query($query);
?>

<h1>Pre-Order Management</h1>
<ul>
    <li><a href="/users/admin/dashboard.php"><i class="fas fa-arrow-left"></i> Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= $_SESSION['message'] ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Pre-Order ID</th>
            <th>User</th>
            <th>Reservation Date</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Items</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['reservation_date'] ? htmlspecialchars($row['reservation_date']) : '<em>N/A</em>' ?></td>
                <td><?= htmlspecialchars($row['order_date']) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <ul style="padding-left: 1rem;">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT m.name, poi.quantity 
                            FROM pre_order_items poi 
                            JOIN menu m ON poi.menu_id = m.id 
                            WHERE poi.pre_order_id = ?
                        ");
                        $stmt->bind_param("i", $row['id']);
                        $stmt->execute();
                        $itemsResult = $stmt->get_result();
                        while ($item = $itemsResult->fetch_assoc()) {
                            echo "<li>" . htmlspecialchars($item['name']) . " (x" . $item['quantity'] . ")</li>";
                        }
                        $stmt->close();
                        ?>
                    </ul>
                </td>
                <td>
                    <div class="action-icons">
                        <!-- Edit -->
                        <a href="/users/admin/edit-pre-order.php?id=<?= $row['id'] ?>" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Status Update -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="status" required>
                                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="prepared" <?= $row['status'] === 'prepared' ? 'selected' : '' ?>>Prepared</option>
                                <option value="served" <?= $row['status'] === 'served' ? 'selected' : '' ?>>Served</option>
                                <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" title="Update Status">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>

                        <!-- Delete -->
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this pre-order?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_pre_order" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="7">No pre-orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</main>
</body>
</html>
