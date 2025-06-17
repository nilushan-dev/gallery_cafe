<?php
include '../../includes/db.php';
include '../staff/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'staff') {
    header('Location: /pages/login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid pre-order ID.";
    exit();
}

$preOrderId = intval($_GET['id']);

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update existing items
    if (isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $itemId => $qty) {
            $qty = intval($qty);
            $itemId = intval($itemId);
            if ($qty > 0) {
                $stmt = $conn->prepare("UPDATE pre_order_items SET quantity = ? WHERE id = ? AND pre_order_id = ?");
                $stmt->bind_param("iii", $qty, $itemId, $preOrderId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Remove items
    if (!empty($_POST['delete_items'])) {
        foreach ($_POST['delete_items'] as $itemId) {
            $itemId = intval($itemId);
            $stmt = $conn->prepare("DELETE FROM pre_order_items WHERE id = ? AND pre_order_id = ?");
            $stmt->bind_param("ii", $itemId, $preOrderId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Add new items
    if (!empty($_POST['new_menu_id']) && !empty($_POST['new_quantity'])) {
        $menuId = intval($_POST['new_menu_id']);
        $quantity = intval($_POST['new_quantity']);
        if ($quantity > 0) {
            $stmt = $conn->prepare("INSERT INTO pre_order_items (pre_order_id, menu_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $preOrderId, $menuId, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    }

    $_SESSION['message'] = "Pre-order updated successfully.";
    header("Location: pre-order-management.php");
    exit();
}

// Get existing pre-order items
$stmt = $conn->prepare("
    SELECT poi.id AS item_id, m.id AS menu_id, m.name, poi.quantity
    FROM pre_order_items poi
    JOIN menu m ON poi.menu_id = m.id
    WHERE poi.pre_order_id = ?
");
$stmt->bind_param("i", $preOrderId);
$stmt->execute();
$itemsResult = $stmt->get_result();
$preOrderItems = $itemsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get menu list
$menuResult = $conn->query("SELECT id, name FROM menu ORDER BY name ASC");
$menuItems = $menuResult->fetch_all(MYSQLI_ASSOC);
?>

<h1>Edit Pre-Order #<?= $preOrderId ?></h1>
<ul>
    <li><a href="pre-order-management.php"><i class="fas fa-arrow-left"></i> Back</a></li>
</ul>

<form method="post">
    <table>
        <thead>
            <tr>
                <th>Menu Item</th>
                <th>Quantity</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($preOrderItems)): ?>
                <tr><td colspan="3">No items in this pre-order.</td></tr>
            <?php else: ?>
                <?php foreach ($preOrderItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>
                            <input type="number" name="quantities[<?= $item['item_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" required>
                        </td>
                        <td>
                            <input type="checkbox" name="delete_items[]" value="<?= $item['item_id'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>Add New Item</h3>
    <div class="add-new-item">
        <label for="new_menu_id">Menu:</label>
        <select name="new_menu_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($menuItems as $menu): ?>
                <option value="<?= $menu['id'] ?>"><?= htmlspecialchars($menu['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="new_quantity">Quantity:</label>
        <input type="number" name="new_quantity" value="1" min="1" required>
    </div>

    <br>
    <button type="submit" class="btn-primary">Save Changes</button>
</form>
</main>
</body>
</html>
