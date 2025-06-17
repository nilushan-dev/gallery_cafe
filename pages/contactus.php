<?php
include '../includes/header.php';
include '../includes/db.php';

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Basic validation (you can expand this)
    if (!empty($name) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contactus (name, subject, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $subject, $message);

        if ($stmt->execute()) {
            echo "<script>alert('Message sent successfully!');</script>";
        } else {
            echo "<script>alert('Error sending message. Please try again.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}
?>

<div class="login-container" id="our-signup">
    <div class="center">
        <h1>Contact Us</h1>
        <form action="contactus.php" method="POST">
            <div class="txt_field">
                <label>Your Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required>
            </div>
            <div class="txt_field">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="Subject of your message" required>
            </div>
            <div class="txt_field">
                <label>Message</label>
                <input type="message" name="message" placeholder="Write your message here..." required>
            </div>
            <input type="submit" value="Send Message">
            <h1>We’d love to hear from you!</h1>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
