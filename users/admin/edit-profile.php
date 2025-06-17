<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

$username = $_SESSION['user'];

// Fetch admin data
$stmt = $conn->prepare("SELECT id, username, nic, phoneno, email FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $nic = trim($_POST['nic']);
    $phone = trim($_POST['phoneno']);
    $email = trim($_POST['email']);

    // Update in DB
    $stmt = $conn->prepare("UPDATE user SET username=?, nic=?, phoneno=?, email=? WHERE id=?");
    $stmt->bind_param("ssssi", $newUsername, $nic, $phone, $email, $admin['id']);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user'] = $newUsername; // update session username if changed
    $_SESSION['message'] = "Profile updated successfully.";
    header("Location: profile.php");
    exit();
}
?>

<h1>Edit Admin Profile</h1>

<ul>
    <li><a href="profile.php">Back to Profile</a></li>
</ul>

<form method="POST">
    <table>
        <tr>
            <th>Username</th>
            <td><input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required></td>
        </tr>
        <tr>
            <th>NIC</th>
            <td><input type="text" name="nic" value="<?= htmlspecialchars($admin['nic']) ?>" required></td>
        </tr>
        <tr>
            <th>Phone No</th>
            <td><input type="text" name="phoneno" value="<?= htmlspecialchars($admin['phoneno']) ?>" required></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
            </td>
        </tr>
    </table>
</form>

</main>
</body>
</html>
