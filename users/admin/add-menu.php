<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

// Optional session check
if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

$message = '';
$error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'] ?? '';
    $cuisine = $_POST['cuisine'] ?? '';

    if ($name === '' || $price <= 0 || $category === '' || $cuisine === '') {
        $error = "Please fill all required fields and select category & cuisine.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Image upload is required.";
    } else {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/menu/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
        $error = "Only JPG, PNG, GIF, and WEBP images allowed.";
        } else {
            $originalName = $_FILES['image']['name'];
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $imageName = md5(time() . $originalName) . '.' . $fileExt;
            $targetFile = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO menu (name, description, price, category, cuisine, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdsss", $name, $description, $price, $category, $cuisine, $imageName);

                if ($stmt->execute()) {
                    $message = "✅ Menu item added successfully.";
                    $name = $description = $category = $cuisine = '';
                    $price = 0;
                } else {
                    $error = "❌ Database error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }
}
?>

<h1>Add Menu Item</h1>

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
            <th>Upload Image</th>
            <td><input type="file" name="image" accept="image/*" required /></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit">➕ Add Menu Item</button>
            </td>
        </tr>
    </table>
</form>

</main>
</body>
</html>
