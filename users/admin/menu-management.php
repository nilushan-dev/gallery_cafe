<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}


// Handle deletion
if (isset($_POST['delete_menu'])) {
    $idToDelete = intval($_POST['id']);
    // Get image filename to delete the file
    $stmt = $conn->prepare("SELECT image_url FROM menu WHERE id=?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->bind_result($imageToDelete);
    $stmt->fetch();
    $stmt->close();

    // Delete image file if exists
    $imagePath = __DIR__ . '/../../assets/images/menu/' . $imageToDelete;
    if ($imageToDelete && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete DB record
    $stmt = $conn->prepare("DELETE FROM menu WHERE id=?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Menu item deleted.";
    header("Location: menu-management.php");
    exit();
}

// Fetch all menu items
$result = $conn->query("SELECT id, name, price, category, cuisine, image_url FROM menu ORDER BY id DESC");

?>
    <h1>Menu Management</h1>
        <ul>
                    <li><a href="/users/admin/add-menu.php"><i class="fas fa-plus"></i> Add Menu Item</a></li>
                    <li><a href="/users/admin/dashboard.php">Back</a></li>
        </ul>
    <?php if (!empty($_SESSION['message'])): ?>
        <p style="color: green;"><?= $_SESSION['message'] ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price (LKR)</th>
                <th>Category</th>
                <th>Cuisine</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($row['image_url'] && file_exists(__DIR__ . '/../../assets/images/menu/' . $row['image_url'])): ?>
                            <img class="menu-img" src="/assets/images/menu/<?= htmlspecialchars($row['image_url']) ?>" alt="Menu Image" />
                        <?php else: ?>
                            <em>No image</em>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>Rs <?= number_format($row['price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['cuisine']) ?></td>
                    <td>
                    <div class="action-icons">
                        <a href="/users/admin/edit-menu.php?id=<?= $row['id'] ?>" title="Edit">
                        <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <button type="submit" name="delete_menu">
                            <i class="fas fa-trash-alt" title="Delete"></i>
                        </button>
                        </form>
                    </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="6">No menu items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

</body>
</html>
