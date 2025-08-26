<?php

include '../../includes/db.php';           // Adjust path if needed
include '../customer/dashboard.php';       // Adjust path if needed


if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'customer') {
    header('Location: /pages/login.php');
    exit();
}

$username = $_SESSION['user'];

// Fetch admin details
$stmt = $conn->prepare("SELECT id, username, nic, phoneno, email, usertype, created_at FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
?>

<h1>Admin Profile</h1>

<ul>
    <li><a href="/users/customer/dashboard.php">Back to Dashboard</a></li>
    <li><a href="logout.php">Logout</a></li>
    <li><a href="/users/customer/edit-profile.php" title="Edit Profile">
        <i class="fas fa-edit"></i> Edit Profile
    </a></li>
</ul>

<?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?= $_SESSION['message'] ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>


<div class="profile-container">
    <table>
        <tr>
            <th>Username</th>
            <td><?= htmlspecialchars($admin['username']) ?></td>
        </tr>
        <tr>
            <th>NIC</th>
            <td><?= htmlspecialchars($admin['nic']) ?></td>
        </tr>
        <tr>
            <th>Phone No</th>
            <td><?= htmlspecialchars($admin['phoneno']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($admin['email']) ?></td>
        </tr>
        <tr>
            <th>User Type</th>
            <td><?= htmlspecialchars($admin['usertype']) ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?= htmlspecialchars($admin['created_at']) ?></td>
        </tr>
    </table>
</div>


</main>
</body>
</html>
