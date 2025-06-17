<?php
session_start();

// Database connection
include '../includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare statement to fetch user info by username or email
    $stmt = $conn->prepare("SELECT id, username, password, usertype FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $dbUsername, $dbPassword, $userType);
        $stmt->fetch();

        // Plain text password check (replace with password_verify if you hash passwords)
        if ($password === $dbPassword) {
            // Set session variables (consistent keys)
            $_SESSION['user_id'] = $userId;
            $_SESSION['user'] = $dbUsername;
            $_SESSION['usertype'] = $userType;

            // Redirect based on user role
            if ($userType === 'admin') {
                header("Location: ../users/admin/user-management.php");
            } elseif ($userType === 'staff') {
                header("Location: ../users/staff/reservation-management.php");
            } else {
                header("Location: ../users/customer/dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "Username or Email not found.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | Gallery Café</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
    <div class="login-container" id="our-login">
        <div class="center">
            <h1>LOGIN</h1>
            <?php if ($error): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST" autocomplete="off">
                <div class="txt_field">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" placeholder="Enter username or email" required />
                </div>
                <div class="txt_field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required />
                </div>
                <input type="submit" value="Login" />
                <div class="signup_link">
                    Not registered yet? <a href="signup.php">Sign up here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
