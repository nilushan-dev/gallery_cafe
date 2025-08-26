<?php
include '../includes/header.php';
include '../includes/db.php';

// Only logged-in customers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'customer') {
    header("Location: /pages/login.php");
    exit();
}

$message = "";

// Handle Reservation Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $datetime = $_POST['reservation_datetime'];
    $guest_count = $_POST['guest_count'];
    $special_requests = $_POST['special_requests'];

    if (!empty($datetime) && !empty($guest_count)) {
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, reservation_date, guest_count, special_requests) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $datetime, $guest_count, $special_requests);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Your reservation has been submitted!');</script>";
        } else {
            echo "<script>alert('❌ Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all required fields.');</script>";
    }
}
?>

<div class="login-container" id="our-signup">
    <div class="center-content">
        <h1>Make a Reservation</h1>
        <form action="reser.php" method="POST">
            <div class="txt_field">
                <label>Date & Time</label>
                <input type="datetime-local" name="reservation_datetime" required>
            </div>
            <div class="txt_field">
                <label>Number of Guests</label>
                <input type="number" name="guest_count" min="1" max="20" placeholder="Enter number of guests" required>
            </div>
            <div class="txt_field">
                <label>Special Requests</label>
                <textarea name="special_requests" placeholder="Optional: Any special requests?" rows="4"></textarea>
            </div>
            <input type="submit" value="Submit Reservation">
            <h1>We look forward to serving you!</h1>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
