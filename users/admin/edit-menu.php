<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

$message = '';
$error = '';

// Dropdown options
$categoryList = [
    '' => '🍽️ ALL',
    'Breakfast' => '🍳 BREAKFAST',
    'Lunch' => '🍛 LUNCH',
    'Dinner' => '🍽️ DINNER',
    'Beverage' => '🥤 BEVERAGE'
];

$cuisineList = [
    '' => '-- Select Cuisine --',
    'Srilankan' => 'Srilankan',
    'Chinese' => 'Chinese',
    'Italian' => 'Italian',
    'Indian' => 'Indian'
];

// Validate and get menu item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid menu item ID.");
}
$id = (int)$_GET['id'];

// Fetch existing menu item
$stmt = $conn->prepare("SELECT name, description, price, category, cuisine, image_url FROM menu WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Menu item not found.");
}

$stmt->bind_result($name, $description, $price, $category, $cuisine, $image_url);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'] ?? '';
    $cuisine = $_POST['cuisine'] ?? '';

    if ($name === '' || $price <= 0 || $category === '' || $cuisine === '') {
        $error = "Please fill all required fields and select category & cuisine.";
    } else {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/menu/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newImageName = $image_url; // Default to existing image

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['image']['type'];

            if (!in_array($fileType, $allowedTypes)) {
                $error = "Only JPG, PNG, GIF images allowed.";
            } else {
                $originalName = $_FILES['image']['name'];
                $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $newImageName = md5(time() . $originalName) . '.' . $fileExt;
                $targetFile = $uploadDir . $newImageName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Delete old image
                    if ($image_url && file_exists($uploadDir . $image_url)) {
                        unlink($uploadDir . $image_url);
                    }
                } else {
                    $error = "Failed to move uploaded file.";
                }
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, cuisine=?, image_url=? WHERE id=?");
            $stmt->bind_param("ssdsssi", $name, $description, $price, $category, $cuisine, $newImageName, $id);

            if ($stmt->execute()) {
                $message = "✅ Menu item updated successfully.";
                $image_url = $newImageName;
            } else {
                $error = "❌ Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h1>Edit Menu Item</h1>

<?php if ($message): ?>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <table>
        <tr>
            <th>Name</th>
            <td><input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required /></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><textarea name="description"><?= htmlspecialchars($description ?? '') ?></textarea></td>
        </tr>
        <tr>
            <th>Price (LKR)</th>
            <td><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price ?? 0) ?>" required /></td>
        </tr>
        <tr>
            <th>Category</th>
            <td>
                <select name="category" required>
                    <?php foreach ($categoryList as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($category ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Cuisine</th>
            <td>
                <select name="cuisine" required>
                    <?php foreach ($cuisineList as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($cuisine ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Current Image</th>
            <td>
                <?php if ($image_url): ?>
                    <img src="/assets/images/menu/<?= htmlspecialchars($image_url) ?>" alt="Menu Image" style="max-width: 150px;">
                <?php else: ?>
                    No image uploaded.
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Replace Image</th>
            <td><input type="file" name="image" accept="image/*" /></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit">💾 Update Menu Item</button>
            </td>
        </tr>
    </table>
</form>

</main>
</body>
</html>
