<?php
include '../../includes/db.php';
include '../staff/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'staff') {
    header('Location: /pages/login.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $idToUpdate = intval($_POST['id']);
    $newStatus = $_POST['status'];
    $stmt = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
    $stmt->bind_param("si", $newStatus, $idToUpdate);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Reservation status updated.";
    header("Location: reservation-management.php");
    exit();
}

// Handle deletion
if (isset($_POST['delete_reservation'])) {
    $idToDelete = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Reservation deleted.";
    header("Location: reservation-management.php");
    exit();
}

// Fetch reservations
$query = "
    SELECT r.id, r.reservation_date, r.guest_count, r.special_requests, r.status, r.created_at, u.username
    FROM reservations r
    LEFT JOIN user u ON r.user_id = u.id
    ORDER BY r.created_at DESC
";
$result = $conn->query($query);
?>

<h1>Reservation Management</h1>

<ul>
    <li><a href="/users/staff/add-reservation.php"><i class="fas fa-plus"></i> Add Reservation</a></li>
    <li><a href="/users/staff/dashboard.php"><i class="fas fa-arrow-left"></i> Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Reservation ID</th>
            <th>User</th>
            <th>Reservation Date</th>
            <th>Guests</th>
            <th>Special Requests</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['reservation_date']))) ?></td>
                <td><?= (int)$row['guest_count'] ?></td>
                <td><?= nl2br(htmlspecialchars($row['special_requests'] ?: '-')) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))) ?></td>
                <td>
                    <div class="action-icons">
                        <!-- Edit -->
                        <a href="/users/staff/edit-reservation.php?id=<?= $row['id'] ?>" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Status Update -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="status" required>
                                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status" title="Update Status">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>

                        <!-- Delete -->
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this reservation?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_reservation" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="8">No reservations found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</main>
</body>
</html>
