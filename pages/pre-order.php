<?php
include '../includes/header.php';
include '../includes/db.php';


if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'customer') {
    header("Location: /pages/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$message = "";

// Handle Pre-Order Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_preorder'])) {
    $arrival_datetime = $_POST['arrival_datetime'];
    $preorder_items = $_POST['items'] ?? [];

    if (!empty($arrival_datetime) && !empty($preorder_items)) {
        $conn->begin_transaction();
        try {
            // Insert into pre_orders
            $stmt = $conn->prepare("INSERT INTO pre_orders (user_id, order_date) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $arrival_datetime);
            $stmt->execute();
            $pre_order_id = $stmt->insert_id;
            $stmt->close();

            // Insert items
            $item_stmt = $conn->prepare("INSERT INTO pre_order_items (pre_order_id, menu_id, quantity) VALUES (?, ?, ?)");
            foreach ($preorder_items as $menu_id => $qty) {
                if ((int)$qty > 0) {
                    $item_stmt->bind_param("iii", $pre_order_id, $menu_id, $qty);
                    $item_stmt->execute();
                }
            }
            $item_stmt->close();

            $conn->commit();
            echo "<script>alert('✅ Pre-order placed successfully!');</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('❌ Failed to place pre-order. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Please select at least one item and arrival time.');</script>";
    }
}

// Filters
$selectedCuisine = $_GET['cuisine'] ?? '';
$selectedCategory = $_GET['category'] ?? '';
?>

<div class="login-container" id="our-signup">
    <div class="center">
        <h1>Pre-Order Your Food</h1>

        <!-- Cuisine Filter -->
        <form method="GET" action="pre-order.php" style="margin-bottom: 15px;">
            <label>Select Cuisine Type:</label>
            <select name="cuisine" onchange="this.form.submit()">
                <option value="">-- All --</option>
                <option value="Srilankan" <?= ($selectedCuisine == 'Srilankan') ? 'selected' : '' ?>>Srilankan</option>
                <option value="Chinese" <?= ($selectedCuisine == 'Chinese') ? 'selected' : '' ?>>Chinese</option>
                <option value="Italian" <?= ($selectedCuisine == 'Italian') ? 'selected' : '' ?>>Italian</option>
                <option value="Indian" <?= ($selectedCuisine == 'Indian') ? 'selected' : '' ?>>Indian</option>
            </select>
        </form>

        <!-- Category Filter -->
        <?php
        if ($selectedCuisine !== '') {
            $categories = [
                '' => '🍽️ All',
                'Breakfast' => '🍳 Breakfast',
                'Lunch' => '🍛 Lunch',
                'Dinner' => '🍽️ Dinner',
                'Beverage' => '🥤 Beverage'
            ];
            echo "<div class='category-links' style='margin-bottom: 20px;'>";
            foreach ($categories as $key => $label) {
                $activeClass = ($key == $selectedCategory) ? 'category-link active' : 'category-link';
                echo "<a href='pre-order.php?cuisine=$selectedCuisine&category=$key' class='$activeClass'>$label</a> ";
            }
            echo "</div>";
        }
        ?>

        <!-- Pre-order Form -->
        <form method="POST" action="pre-order.php">
            <div class="txt_field">
                <label>Select Arrival Date & Time</label>
                <input type="datetime-local" name="arrival_datetime" required>
            </div>

            <div class="txt_field">
                <label>Select Items and Quantity</label>
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                    <?php
                    $conditions = [];
                    if (!empty($selectedCuisine)) {
                        $conditions[] = "cuisine = '" . $conn->real_escape_string($selectedCuisine) . "'";
                    }
                    if (!empty($selectedCategory)) {
                        $conditions[] = "category = '" . $conn->real_escape_string($selectedCategory) . "'";
                    }

                    $sql = "SELECT * FROM menu";
                    if (!empty($conditions)) {
                        $sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<div style='margin-bottom: 10px;'>";
                            echo "<strong>" . htmlspecialchars($row['name']) . "</strong> - Rs. " . htmlspecialchars($row['price']) . "<br>";
                            echo "<input type='number' name='items[" . $row['id'] . "]' min='0' max='10' placeholder='Qty' style='width: 60px;'> ";
                            echo "<span style='font-size: 12px; color: gray;'>(" . htmlspecialchars($row['category']) . " - " . htmlspecialchars($row['cuisine']) . ")</span>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No menu items found.</p>";
                    }
                    ?>
                </div>
            </div>

            <input type="submit" name="submit_preorder" value="Submit Pre-Order">
            <h1>We’ll start preparing before you arrive!</h1>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
