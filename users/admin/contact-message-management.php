<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// Handle deletion
if (isset($_POST['delete_contact'])) {
    $idToDelete = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM contactus WHERE id=?");
    $stmt->bind_param("i", $idToDelete);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Contact message deleted.";
    header("Location: contactus-management.php");
    exit();
}

// Fetch all contact messages
$result = $conn->query("SELECT id, name, subject, message, created_at FROM contactus ORDER BY id DESC");

?>
<h1>Contact Messages Management</h1>
<ul>
    <li><a href="/users/admin/dashboard.php">Back</a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= $_SESSION['message'] ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <div class="action-icons">
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                            <button type="submit" name="delete_contact">
                                <i class="fas fa-trash-alt" title="Delete"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="5">No contact messages found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</main>
</body>
</html>
