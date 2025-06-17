<?php
include '../../includes/db.php';
include '/users/staff/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'staff') {
    header('Location: /pages/login.php');
    exit();
}

$username = $_SESSION['user'];

// Fetch staff details
$stmt = $conn->prepare("SELECT id, username, nic, phoneno, email, usertype, created_at FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$stmt->close();
?>

<h1>staff Profile</h1>

<ul>
    <li><a href="/users/staff/dashboard.php">Back to Dashboard</a></li>
    <li><a href="/pages/logout.php">Logout</a></li>
    <li><a href="/users/staff/edit-profile.php" title="Edit Profile">
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
            <td><?= htmlspecialchars($staff['username']) ?></td>
        </tr>
        <tr>
            <th>NIC</th>
            <td><?= htmlspecialchars($staff['nic']) ?></td>
        </tr>
        <tr>
            <th>Phone No</th>
            <td><?= htmlspecialchars($staff['phoneno']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($staff['email']) ?></td>
        </tr>
        <tr>
            <th>User Type</th>
            <td><?= htmlspecialchars($staff['usertype']) ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?= htmlspecialchars($staff['created_at']) ?></td>
        </tr>
    </table>
</div>


</main>
</body>
</html>
