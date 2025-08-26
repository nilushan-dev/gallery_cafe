<?php

include '../../includes/db.php';           // Adjust path if needed
include '../customer/dashboard.php';       // Adjust path if needed

// Check if logged-in and is customer
if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'customer') {
    header('Location: /pages/login.php');
    exit();
}

// Get logged-in user's ID
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /pages/login.php');
    exit();
}

// Handle deletion
if (isset($_POST['delete_pre_order'])) {
    $idToDelete = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM pre_orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $idToDelete, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Pre-order deleted.";
    header("Location: view-pre-orders.php");
    exit();
}

// Fetch this user's pre-orders along with their items and menu names
// We'll fetch pre-orders first, then fetch items for each pre-order below to keep it simple

$stmt = $conn->prepare("SELECT id, reservation_id, order_date, status FROM pre_orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pre_orders_result = $stmt->get_result();
?>


<h1>My Pre-Orders</h1>
<ul>
    <li><a href="/pages/pre-order.php"><i class="fas fa-plus"></i> Add Pre-Order</a></li>
    <li><a href="/users/customer/dashboard.php">Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Order Date</th>
            <th>Reservation ID</th>
            <th>Items</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($pre_orders_result && $pre_orders_result->num_rows > 0): ?>
        <?php while ($pre_order = $pre_orders_result->fetch_assoc()): ?>
            <?php
                // Fetch items for this pre_order
                $stmt_items = $conn->prepare("SELECT poi.quantity, m.name 
                                              FROM pre_order_items poi 
                                              JOIN menu m ON poi.menu_id = m.id 
                                              WHERE poi.pre_order_id = ?");
                $stmt_items->bind_param("i", $pre_order['id']);
                $stmt_items->execute();
                $items_result = $stmt_items->get_result();
                $items_list = [];
                while ($item = $items_result->fetch_assoc()) {
                    $items_list[] = htmlspecialchars($item['name']) . " (x" . (int)$item['quantity'] . ")";
                }
                $stmt_items->close();
            ?>
            <tr>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($pre_order['order_date']))) ?></td>
                <td><?= $pre_order['reservation_id'] ? (int)$pre_order['reservation_id'] : '-' ?></td>
                <td><?= !empty($items_list) ? implode("<br>", $items_list) : '-' ?></td>
                <td><?= htmlspecialchars(ucfirst($pre_order['status'])) ?></td>
                <td>
                    <a href="/users/customer/edit-pre-order.php?id=<?= $pre_order['id'] ?>" title="Edit"> <i class="fas fa-edit"></i></a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pre-order?');">
                        <input type="hidden" name="id" value="<?= $pre_order['id'] ?>" />
                            <button type="submit" name="delete_pre_order" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No pre-orders found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
