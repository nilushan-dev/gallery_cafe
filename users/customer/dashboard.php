
<?php
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/../../includes/header.php';




$user_id = $_SESSION['user_id'];
$username = $_SESSION['user'];

$section = $_GET['section'] ?? 'dashboard';
$action  = $_GET['action']  ?? 'list';

function clean($data) {
    return htmlspecialchars(trim($data));
}
?>
<div class="site-content">
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>

    <nav>
        <a href="?section=dashboard">🏠 Dashboard</a> |
        <a href="?section=reservations">📅 Reservations</a> |
        <a href="?section=preorders">🛒 Pre-Orders</a> |
        <a href="/pages/profile.php">👤 Profile</a> |
        <a href="/logout.php">🚪 Logout</a>
    </nav>
    <hr>

<?php
// ---------------- DASHBOARD ----------------
if ($section === 'dashboard'):

    // Total Reservations
    $reservation_count = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE user_id = $user_id")->fetch_assoc()['total'];

    // Total Pre-Orders
    $preorder_count = $conn->query("SELECT COUNT(*) AS total FROM pre_orders WHERE user_id = $user_id")->fetch_assoc()['total'];

    // Most Pre-Ordered Item
    $most_ordered_item_query = $conn->query("
        SELECT m.name, SUM(poi.quantity) AS total_qty 
        FROM pre_orders po
        JOIN pre_order_items poi ON po.id = poi.pre_order_id
        JOIN menu m ON poi.menu_id = m.id
        WHERE po.user_id = $user_id
        GROUP BY poi.menu_id
        ORDER BY total_qty DESC
        LIMIT 1
    ");
    $most_ordered = $most_ordered_item_query->fetch_assoc();
?>
    <h2>Customer Dashboard</h2>
    <div style="background-color: #f2f2f2; border: 1px solid #ccc; padding: 15px; border-radius: 10px; max-width: 400px;">
        <p><strong>🗓️ Total Reservations:</strong> <?= $reservation_count ?></p>
        <p><strong>🛒 Total Pre-Orders:</strong> <?= $preorder_count ?></p>
        <p><strong>🍽️ Most Pre-Ordered Food:</strong> 
            <?= $most_ordered ? htmlspecialchars($most_ordered['name']) . " (" . $most_ordered['total_qty'] . " times)" : "No orders yet." ?>
        </p>
    </div>
<?php
// Show latest reservation confirmation
$latest = $conn->query("SELECT reservation_date, status FROM reservations WHERE user_id = $user_id ORDER BY reservation_date DESC LIMIT 1");

if ($latest && $latest->num_rows > 0) {
    $res = $latest->fetch_assoc();
    if ($res['status'] === 'confirmed') {
        echo "<div style='margin-top: 20px; padding: 10px; background: #e0ffe0; border: 1px solid #00cc00; border-radius: 5px;'>
                ✅ <strong>Your reservation on " . date('F j, Y \a\t g:i A', strtotime($res['reservation_date'])) . " is confirmed!</strong>
              </div>";
    } elseif ($res['status'] === 'pending') {
        echo "<div style='margin-top: 20px; padding: 10px; background: #fff4e0; border: 1px solid #ffaa00; border-radius: 5px;'>
                ⏳ <strong>Your latest reservation is still pending confirmation.</strong>
              </div>";
    } elseif ($res['status'] === 'cancelled') {
        echo "<div style='margin-top: 20px; padding: 10px; background: #ffe0e0; border: 1px solid #cc0000; border-radius: 5px;'>
                ❌ <strong>Your last reservation was cancelled.</strong>
              </div>";
    }
}
?>


    <?php
    // ---------------- RESERVATIONS ----------------
    elseif ($section === 'reservations'):
        if ($action === 'list'):
            $result = $conn->query("SELECT * FROM reservations WHERE user_id=$user_id ORDER BY reservation_date DESC");
    ?>
        <h2>My Reservations <a href="?section=reservations&action=add">➕ Add New</a></h2>
        <?php if ($result->num_rows === 0): ?>
            <p>No reservations yet.</p>
        <?php else: ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Date</th><th>Guests</th><th>Requests</th><th>Status</th><th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['reservation_date'] ?></td>
                        <td><?= $row['guest_count'] ?></td>
                        <td><?= htmlspecialchars($row['special_requests']) ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <a href="?section=reservations&action=edit&id=<?= $row['id'] ?>">✏️ Edit</a> |
                            <a href="?section=reservations&action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Delete this reservation?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>

    <?php elseif ($action === 'add'): ?>
        <h2>Add Reservation</h2>
        <form method="post" action="?section=reservations&action=add">
            <label>Date & Time: <input type="datetime-local" name="reservation_date" required></label><br>
            <label>Guest Count: <input type="number" name="guest_count" min="1" required></label><br>
            <label>Special Requests:<br><textarea name="special_requests"></textarea></label><br>
            <button type="submit">Save</button>
        </form>
        <p><a href="?section=reservations">⬅ Back</a></p>

    <?php elseif ($action === 'edit' && isset($_GET['id'])):
        $rid = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $rid, $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
    ?>
        <h2>Edit Reservation</h2>
        <form method="post" action="?section=reservations&action=edit&id=<?= $rid ?>">
            <label>Date & Time: <input type="datetime-local" name="reservation_date" value="<?= date('Y-m-d\TH:i', strtotime($res['reservation_date'])) ?>" required></label><br>
            <label>Guest Count: <input type="number" name="guest_count" value="<?= $res['guest_count'] ?>" required></label><br>
            <label>Special Requests:<br><textarea name="special_requests"><?= htmlspecialchars($res['special_requests']) ?></textarea></label><br>
            <label>Status:
                <select name="status">
                    <?php foreach (['pending','confirmed','cancelled','completed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $res['status']==$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <button type="submit">Update</button>
        </form>
        <p><a href="?section=reservations">⬅ Back</a></p>
    <?php endif; ?>


    <?php
    // ---------------- PRE-ORDERS ----------------
    elseif ($section === 'preorders'):
        if ($action === 'list'):
            $orders = $conn->query("SELECT * FROM pre_orders WHERE user_id=$user_id ORDER BY order_date DESC");
    ?>
        <h2>My Pre-Orders <a href="?section=preorders&action=add">➕ Add New</a></h2>
        <?php if ($orders->num_rows === 0): ?>
            <p>No pre-orders yet.</p>
        <?php else: ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Date</th><th>Status</th><th>Items</th><th>Actions</th>
                </tr>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= $order['status'] ?></td>
                        <td>
                            <ul>
                                <?php
                                $oid = $order['id'];
                                $items = $conn->query("SELECT poi.quantity, m.name FROM pre_order_items poi JOIN menu m ON poi.menu_id = m.id WHERE poi.pre_order_id = $oid");
                                while ($item = $items->fetch_assoc()):
                                ?>
                                    <li><?= $item['quantity'] ?> x <?= $item['name'] ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </td>
                        <td>
                            <a href="?section=preorders&action=edit&id=<?= $order['id'] ?>">✏️ Edit</a> |
                            <a href="?section=preorders&action=delete&id=<?= $order['id'] ?>" onclick="return confirm('Delete this pre-order?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>

    <?php elseif ($action === 'add'): ?>
        <?php $menu = $conn->query("SELECT id, name FROM menu"); ?>
        <h2>Add Pre-Order</h2>
        <form method="post" action="?section=preorders&action=add">
            <div id="items">
                <div>
                    <select name="menu_id[]">
                        <?php while($m = $menu->fetch_assoc()): ?>
                            <option value="<?= $m['id'] ?>"><?= $m['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="quantity[]" value="1" min="1">
                </div>
            </div>
            <button type="button" onclick="addRow()">Add Item</button><br><br>
            <button type="submit">Save</button>
        </form>
        <p><a href="?section=preorders">⬅ Back</a></p>

        <script>
            function addRow() {
                const container = document.getElementById('items');
                const newItem = container.children[0].cloneNode(true);
                container.appendChild(newItem);
            }
        </script>

    <?php elseif ($action === 'edit' && isset($_GET['id'])):
        $pid = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM pre_orders WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $pid, $user_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
    ?>
        <h2>Edit Pre-Order</h2>
        <form method="post" action="?section=preorders&action=edit&id=<?= $pid ?>">
            <label>Status:
                <select name="status">
                    <?php foreach(['pending', 'prepared', 'served', 'cancelled'] as $st): ?>
                        <option value="<?= $st ?>" <?= $order['status'] == $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>
            <button type="submit">Update</button>
        </form>
        <p><a href="?section=preorders">⬅ Back</a></p>
    <?php endif; ?>
    <?php endif; ?>
</div>

