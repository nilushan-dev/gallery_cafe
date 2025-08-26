
<?php
// Start session and include header and database connection
include '../includes/header.php';
include '../includes/db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION["user"]) && $_SESSION["user"] != "";
?>

<div class="site-content">

    <!-- Top icons/images -->
    <div class="welcome_menu">
        <ul>
            <li><img src="../assets/icons/6823544.jpg" alt="icon-1" class="menu_image"></li>
            <li><img src="../assets/icons/fc0a13d0-ed78-4e1a-a7e1-4e8bc76c2613.jpg" alt="icon-2" class="menu_image"></li>
            <li><img src="../assets/icons/b1.jpg" alt="icon-3" class="menu_image"></li>
        </ul>
    </div>

    <h1>OUR MENU PAGE</h1>

    <!-- Cuisine Filter Dropdown -->
    <form method="GET" action="menu.php">
        <label>Select Cuisine Type:</label>
        <select name="cuisine" onchange="this.form.submit()">
            <option value="">-- All --</option>
            <option value="Srilankan" <?= (($_GET['cuisine'] ?? '') == 'Srilankan') ? 'selected' : '' ?>>Srilankan</option>
            <option value="Chinese" <?= (($_GET['cuisine'] ?? '') == 'Chinese') ? 'selected' : '' ?>>Chinese</option>
            <option value="Italian" <?= (($_GET['cuisine'] ?? '') == 'Italian') ? 'selected' : '' ?>>Italian</option>
            <option value="Indian" <?= (($_GET['cuisine'] ?? '') == 'Indian') ? 'selected' : '' ?>>Indian</option>
        </select>
    </form>

    <hr>

    <?php
    // Get filters from URL
    $selectedCuisine = $_GET['cuisine'] ?? '';
    $selectedCategory = $_GET['category'] ?? '';

    // Show categories only if a cuisine is selected
    if ($selectedCuisine != '') {
        echo "<h3>Showing items for: <span style='color: green;'>$selectedCuisine</span></h3>";

        // Categories list
        $categoryList = [
            '' => '🍽️ ALL',
            'Breakfast' => '🍳 BREAKFAST',
            'Lunch' => '🍛 LUNCH',
            'Dinner' => '🍽️ DINNER',
            'Beverage' => '🥤 BEVERAGE'
        ];

        // Display category links
        echo "<div style='margin-bottom: 20px;'>";
        echo "<div class='category-links'>";
        foreach ($categoryList as $key => $label) {
            $activeClass = ($key == $selectedCategory) ? "category-link active" : "category-link";
            echo "<a href='menu.php?cuisine=$selectedCuisine&category=$key' class='$activeClass'>$label</a>";
        }
        echo "</div></div>";
    }

    // Prepare SQL query based on filters
    $sql = "SELECT * FROM menu";
    $conditions = [];

    if ($selectedCuisine != '') {
        $conditions[] = "cuisine = '" . $conn->real_escape_string($selectedCuisine) . "'";
    }

    if ($selectedCategory != '') {
        $conditions[] = "category = '" . $conn->real_escape_string($selectedCategory) . "'";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $result = $conn->query($sql);

    // Show menu items
    if ($result && $result->num_rows > 0) {
        echo "<div class='menu-items'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='menu-item'>";
            echo "<h4>" . htmlspecialchars($row['name']) . "</h4>";
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            echo "<strong>Rs. " . htmlspecialchars($row['price']) . "</strong><br>";

            if (!empty($row['image_url'])) {
                echo "<img src='../assets/images/menu/" . htmlspecialchars($row['image_url']) . "' alt='menu image'><br>";
            }

            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No items found for the selected filters.</p>";
    }

    $conn->close();
    ?>

</div>

<?php include '../includes/footer.php'; ?>
