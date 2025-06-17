<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

$message = '';
$error = '';

// Validate reservation ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid reservation ID.");
}
$id = (int)$_GET['id'];

// Fetch reservation data
$stmt = $conn->prepare("SELECT user_id, reservation_date, guest_count, special_requests, status FROM reservations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Reservation not found.");
}

$stmt->bind_result($user_id, $reservation_date, $guest_count, $special_requests, $status);
$stmt->fetch();
$stmt->close();

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
        $stmt = $conn->prepare("UPDATE reservations SET user_id=?, reservation_date=?, guest_count=?, special_requests=?, status=? WHERE id=?");
        $stmt->bind_param("isisss", $user_id, $reservation_date, $guest_count, $special_requests, $status, $id);

        if ($stmt->execute()) {
            $message = "✅ Reservation updated successfully.";
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1>Edit Reservation</h1>

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
                <option value="<?= $uid ?>" <?= ($user_id == $uid) ? 'selected' : '' ?>><?= htmlspecialchars($uname) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>
        Reservation Date & Time:<br>
        <input type="datetime-local" name="reservation_date" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($reservation_date))) ?>" required />
    </label><br><br>

    <label>
        Number of Guests:<br>
        <input type="number" min="1" name="guest_count" value="<?= (int)$guest_count ?>" required />
    </label><br><br>

    <label>
        Special Requests:<br>
        <textarea name="special_requests"><?= htmlspecialchars($special_requests) ?></textarea>
    </label><br><br>

    <label>
        Status:<br>
        <select name="status" required>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($status === $key) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <button type="submit">💾 Update Reservation</button>
</form>

</main>
</body>
</html>
