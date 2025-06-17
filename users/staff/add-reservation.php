<?php
include '../../includes/db.php';
include '/users/staff/dashboard.php';

$message = '';
$error = '';

// Fetch all users to populate user dropdown
$users = [];
$userResult = $conn->query("SELECT id, username FROM `user` ORDER BY username");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[$row['id']] = $row['username'];
    }
}

$statuses = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $reservation_date = trim($_POST['reservation_date'] ?? '');
    $guest_count = (int)($_POST['guest_count'] ?? 0);
    $special_requests = trim($_POST['special_requests'] ?? '');
    $status = $_POST['status'] ?? '';

    // Basic validation
    if ($user_id <= 0 || !isset($users[$user_id]) || !$reservation_date || $guest_count <= 0 || !isset($statuses[$status])) {
        $error = "Please fill in all required fields correctly.";
    } elseif (strtotime($reservation_date) === false) {
        $error = "Invalid date/time format.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, reservation_date, guest_count, special_requests, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $user_id, $reservation_date, $guest_count, $special_requests, $status);

        if ($stmt->execute()) {
            $message = "✅ Reservation added successfully.";
            // Clear form values after success
            $user_id = $reservation_date = $guest_count = $special_requests = $status = '';
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1>Add Reservation</h1>

<?php if ($message): ?>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <label>
        User:<br>
        <select name="user_id" required>
            <option value="">Select user</option>
            <?php foreach ($users as $uid => $uname): ?>
                <option value="<?= $uid ?>" <?= (isset($user_id) && $user_id == $uid) ? 'selected' : '' ?>><?= htmlspecialchars($uname) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>
        Reservation Date & Time:<br>
        <input type="datetime-local" name="reservation_date" value="<?= htmlspecialchars($reservation_date ?? '') ?>" required />
    </label><br><br>

    <label>
        Number of Guests:<br>
        <input type="number" min="1" name="guest_count" value="<?= (int)($guest_count ?? 0) ?>" required />
    </label><br><br>

    <label>
        Special Requests:<br>
        <textarea name="special_requests"><?= htmlspecialchars($special_requests ?? '') ?></textarea>
    </label><br><br>

    <label>
        Status:<br>
        <select name="status" required>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= $key ?>" <?= (isset($status) && $status === $key) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <button type="submit">➕ Add Reservation</button>
</form>

</main>
</body>
</html>
