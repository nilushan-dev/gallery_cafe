<?php
include '../includes/db.php';
include '../includes/header.php';
// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['usertype'])) {
    header("Location: login.php");
    exit();
}



$user_id = $_SESSION['user_id'];
$usertype = $_SESSION['usertype'];
$message = "";

// Get current user data
$stmt = $conn->prepare("SELECT username, nic, phoneno, email FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $nic, $phoneno, $email);
$stmt->fetch();
$stmt->close();

// On form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_nic = trim($_POST['nic']);
    $new_phone = trim($_POST['phoneno']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET username=?, nic=?, phoneno=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssssi", $new_username, $new_nic, $new_phone, $new_email, $hashed, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE user SET username=?, nic=?, phoneno=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $new_username, $new_nic, $new_phone, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully.";
        $_SESSION['user'] = $new_username;
    } else {
        $message = "❌ Failed to update profile.";
    }
    $stmt->close();

    // Refresh values
    $username = $new_username;
    $nic = $new_nic;
    $phoneno = $new_phone;
    $email = $new_email;
}
?>

<div class="site-content">
    <h2>👤 <?= ucfirst($usertype) ?> Profile</h2>

    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:<br>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
        </label><br><br>

        <label>NIC:<br>
            <input type="text" name="nic" value="<?= htmlspecialchars($nic) ?>" required>
        </label><br><br>

        <label>Phone No:<br>
            <input type="text" name="phoneno" value="<?= htmlspecialchars($phoneno) ?>" required>
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </label><br><br>

        <label>New Password (leave blank to keep current):<br>
            <input type="password" name="password">
        </label><br><br>

        <button type="submit">💾 Update Profile</button>
    </form>

    <p><a href="/users/<?= $usertype ?>/dashboard.php">⬅ Back to <?= ucfirst($usertype) ?> Dashboard</a></p>
</div>

<?php include '../includes/footer.php'; ?>
