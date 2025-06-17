
<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// Handle deletion
if (isset($_POST['delete_reservation'])) {
    $idToDelete = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id=?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Reservation deleted.";
    header("Location: reservation-management.php");
    exit();
}

// Fetch all reservations with user info (optional join)
$sql = "SELECT r.id, r.reservation_date, r.guest_count, r.special_requests, r.status, r.created_at, u.username 
        FROM reservations r
        LEFT JOIN `user` u ON r.user_id = u.id
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);

?>

<h1>Reservation Management</h1>
<ul>
    <li><a href="/users/admin/add-reservation.php"><i class="fas fa-plus"></i> Add Reservation</a></li>
    <li><a href="/users/admin/dashboard.php">Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['message']) ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
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
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['reservation_date']))) ?></td>
                    <td><?= (int)$row['guest_count'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['special_requests'] ?: '-')) ?></td>
                    <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))) ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="/users/admin/edit-reservation.php?id=<?= $row['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this reservation?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                                <button type="submit" name="delete_reservation" style="background:none;border:none;cursor:pointer;">
                                    <i class="fas fa-trash-alt" title="Delete"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No reservations found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</main>

</body>
</html>
