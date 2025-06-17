<?php
session_start();
$connection = mysqli_connect("localhost", "root", "", "gallery_cafe_r_db");
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $nic = mysqli_real_escape_string($connection, trim($_POST['nic']));
    $phoneno = mysqli_real_escape_string($connection, trim($_POST['phoneno']));
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);

    if ($password === $cpassword) {
        // Check if username or email already exists
        $checkUser = $connection->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $checkUser->bind_param("ss", $username, $email);
        $checkUser->execute();
        $checkUser->store_result();

        if ($checkUser->num_rows > 0) {
            echo "<script>alert('Username or Email already exists!');</script>";
        } else {
            $stmt = $connection->prepare("INSERT INTO user (username, nic, phoneno, email, password, usertype) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param("sssss", $username, $nic, $phoneno, $email, $password);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Redirecting to login...');</script>";
                echo "<script>window.location.href = '../pages/login.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: " . htmlspecialchars($stmt->error) . "');</script>";
            }

            $stmt->close();
        }

        $checkUser->close();
    } else {
        echo "<script>alert('Passwords do not match!');</script>";
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gallery Cafe - Signup</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/imagestyle.css" />
</head>
<body>
    <div class="login-container" id="our-signup">
        <div class="center">
            <h1>REGISTER</h1>
            <form action="signup.php" method="POST">
                <div class="txt_field">
                    <label>Name</label>
                    <input type="text" name="username" placeholder="Enter your full name" required />
                </div>
                <div class="txt_field">
                    <label>NIC</label>
                    <input type="text" name="nic" placeholder="National Identity Card Number" required />
                </div>
                <div class="txt_field">
                    <label>Phone Number</label>
                    <input type="tel" name="phoneno" placeholder="Enter your phone number" pattern="[0-9]{10}" required />
                </div>
                <div class="txt_field">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email address" required />
                </div>
                <div class="txt_field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter a secure password" minlength="8" required />
                </div>
                <div class="txt_field">
                    <label>Confirm Password</label>
                    <input type="password" name="cpassword" placeholder="Re-enter your password" required />
                </div>
                <input type="submit" value="Sign Up" />
                <div class="signup_link">
                    Already have an account? <a href="../pages/login.php">Login here</a>
                </div>
                <h1>Please fill the form to register!</h1>
            </form>
        </div>
    </div>
</body>
</html>
