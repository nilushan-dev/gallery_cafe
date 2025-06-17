<?php
include '../../includes/db.php';
include '../admin/dashboard.php';

if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}

// Initialize messages
$message = '';
$error = '';

// Default values
$username = $nic = $phoneno = $email = $usertype = '';
$password = '';

$userTypes = [
    '' => '-- Select User Type --',
    'admin' => 'Admin',
    'customer' => 'Customer'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nic = trim($_POST['nic']);
    $phoneno = trim($_POST['phoneno']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $usertype = $_POST['usertype'] ?? '';

    if ($username === '' || $nic === '' || $phoneno === '' || $email === '' || $password === '' || $usertype === '') {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO user (username, nic, phoneno, email, password, usertype) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $nic, $phoneno, $email, $hashedPassword, $usertype);

        if ($stmt->execute()) {
            $message = "✅ User added successfully.";
            $username = $nic = $phoneno = $email = $usertype = '';
            $password = '';
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1>Add New User</h1>

<ul>
    <li><a href="dashboard.php">Back to Dashboard</a></li>
</ul>

<?php if ($message): ?>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
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
            <td><input type="password" name="password" required></td>
        </tr>
        <tr>
            <th>User Type</th>
            <td>
                <select name="usertype" required>
                    <?php foreach ($userTypes as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($usertype === $value) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit"><i class="fas fa-user-plus"></i> Add User</button>
            </td>
        </tr>
    </table>
</form>

</main>
</body>
</html>
