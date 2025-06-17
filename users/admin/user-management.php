<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $idToDelete = intval($_POST['id']);

    // Prevent deleting the logged-in admin
    if ($_SESSION['user']['id'] == $idToDelete) {
        $_SESSION['message'] = "You cannot delete your own account.";
        header("Location: user-management.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "User deleted.";
    header("Location: user-management.php");
    exit();
}

// Fetch all users
$result = $conn->query("SELECT id, username, nic, phoneno, email, usertype, created_at FROM user ORDER BY id DESC");
?>
<h1>User Management</h1>
<ul>
    <li><a href="/users/admin/add-user.php"><i class="fas fa-user-plus"></i> Add User</a></li>
    <li><a href="/users/admin/dashboard.php">Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= $_SESSION['message'] ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>NIC</th>
            <th>Phone</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['nic']) ?></td>
                <td><?= htmlspecialchars($row['phoneno']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['usertype']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <div class="action-icons">
                        <a href="/users/admin/edit-user.php?id=<?= $row['id'] ?>" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                            <button type="submit" name="delete_user">
                                <i class="fas fa-trash-alt" title="Delete"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="7">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</main>

</body>
</html>
