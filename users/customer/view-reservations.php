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
if (isset($_POST['delete_reservation'])) {
    $idToDelete = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $idToDelete, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Reservation deleted.";
    header("Location: view-reservations.php");
    exit();
}

// Fetch this user's reservations
$stmt = $conn->prepare("SELECT id, reservation_date, guest_count, special_requests, status, created_at 
                        FROM reservations 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Reservations</title>
    <link rel="stylesheet" href="/path-to-your-css/styles.css" />
    <!-- Add your CSS or JS here -->
</head>
<body>

<h1>My Reservations</h1>
<ul>
    <li><a href="/pages/reser.php"><i class="fas fa-plus"></i> Add Reservation</a></li>
    <li><a href="/users/customer/dashboard.php">Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Date</th>
            <th>Guests</th>
            <th>Special Requests</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['reservation_date']))) ?></td>
                <td><?= (int)$row['guest_count'] ?></td>
                <td><?= nl2br(htmlspecialchars($row['special_requests'] ?: '-')) ?></td>
                <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))) ?></td>
                <td>
                    <a href="/users/customer/edit-reservation.php?id=<?= $row['id'] ?>" title="Edit">✏️ Edit</a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                        <button type="submit" name="delete_reservation" style="background:none;border:none;color:red;cursor:pointer;">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No reservations found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
