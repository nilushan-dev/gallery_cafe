<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

$message = '';
$error = '';

// Validate user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}
$id = (int)$_GET['id'];

// Fetch user data
$stmt = $conn->prepare("SELECT username, nic, phoneno, email, usertype FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("User not found.");
}

$stmt->bind_result($username, $nic, $phoneno, $email, $usertype);
$stmt->fetch();
$stmt->close();

$userTypes = [
    'admin' => 'Admin',
    'customer' => 'Customer'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nic = trim($_POST['nic']);
    $phoneno = trim($_POST['phoneno']);
    $email = trim($_POST['email']);
    $usertype = $_POST['usertype'] ?? '';
    $newPassword = $_POST['password'];

    if ($username === '' || $nic === '' || $phoneno === '' || $email === '' || $usertype === '') {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        if ($newPassword !== '') {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET username=?, nic=?, phoneno=?, email=?, password=?, usertype=? WHERE id=?");
            $stmt->bind_param("ssssssi", $username, $nic, $phoneno, $email, $hashedPassword, $usertype, $id);
        } else {
            $stmt = $conn->prepare("UPDATE user SET username=?, nic=?, phoneno=?, email=?, usertype=? WHERE id=?");
            $stmt->bind_param("sssssi", $username, $nic, $phoneno, $email, $usertype, $id);
        }

        if ($stmt->execute()) {
            $message = "✅ User updated successfully.";
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<h1>Edit User</h1>

<ul>
    <li><a href="user-management.php">Back to User List</a></li>
</ul>

<?php if ($message): ?>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <table>
        <tr>
            <th>Username</th>
            <td><input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required></td>
        </tr>
        <tr>
            <th>NIC</th>
            <td><input type="text" name="nic" value="<?= htmlspecialchars($nic) ?>" required></td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td><input type="text" name="phoneno" value="<?= htmlspecialchars($phoneno) ?>" required></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></td>
        </tr>
        <tr>
            <th>Password</th>
            <td><input type="password" name="password" placeholder="Leave blank to keep current"></td>
        </tr>
        <tr>
            <th>User Type</th>
            <td>
                <select name="usertype" required>
                    <?php foreach ($userTypes as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($usertype === $key) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit"><i class="fas fa-save"></i> Update User</button>
            </td>
        </tr>
    </table>
</form>

</main>
</body>
</html>
