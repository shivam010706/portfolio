<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Check if user is admin (admins cannot book)
if (isAdmin()) {
    header('Location: index.php');
    exit();
}

$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$messageType = '';

if ($vehicle_id === 0) {
    header('Location: index.php');
    exit();
}

// Get vehicle details
$vehicle_query = "SELECT * FROM vehicles WHERE id = ? AND status = 'available'";
$stmt = $conn->prepare($vehicle_query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle_result = $stmt->get_result();

if ($vehicle_result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$vehicle = $vehicle_result->fetch_assoc();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $rental_days = intval($_POST['rental_days']);
    $user_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($customer_name) || empty($customer_email) || 
        empty($customer_phone) || $rental_days < 1) {
        $message = 'Please fill in all fields correctly.';
        $messageType = 'error';
    } else {
        $total_cost = $vehicle['daily_rate'] * $rental_days;
        
        // Insert booking with pending status and user_id
        $booking_query = "INSERT INTO bookings (user_id, vehicle_id, customer_name, customer_email, customer_phone, rental_days, total_cost, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($booking_query);
        $stmt->bind_param("iisssid", $user_id, $vehicle_id, $customer_name, $customer_email, $customer_phone, $rental_days, $total_cost);

        if ($stmt->execute()) {
            $message = "Booking request submitted! Your booking is pending approval. Total cost: ₹" . number_format($total_cost, 2);
            $messageType = 'success';

            // Redirect to index after successful booking
            header("refresh:3;url=index.php");
        } else {
            $message = 'Error processing booking. Please try again.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($vehicle['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="header-left">
                    <h1>Book Your Vehicle</h1>
                    <p>Elite Wheels (Vehicle on Rent)</p>
                    <p>Complete your booking for <?php echo htmlspecialchars($vehicle['name']); ?></p>
                </div>
                <div class="header-right">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-info">
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <a href="logout.php" class="btn btn-small btn-secondary">Logout</a>
                            <?php if (isAdmin()): ?>
                                <a href="admin.php" class="btn btn-small btn-primary">Admin Panel</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn btn-small btn-primary">Login</a>
                            <a href="register.php" class="btn btn-small btn-secondary">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($messageType !== 'success'): ?>
            <div class="booking-section">
                <div class="vehicle-summary">
                    <div class="vehicle-image-large">
                        <?php if (!empty($vehicle['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($vehicle['image_url']); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>">
                        <?php else: ?>
                            <div class="vehicle-icon-large">
                                <span class="vehicle-type-icon"><?php echo ucfirst($vehicle['type']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($vehicle['name']); ?></h2>
                    <p class="vehicle-type"><?php echo ucfirst($vehicle['type']); ?></p>
                    <p class="vehicle-price">₹<?php echo number_format($vehicle['daily_rate'], 2); ?> / day</p>
                </div>

                <div class="booking-form-container">
                    <h2>Booking Details</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="customer_name">Full Name:</label>
                            <input type="text" id="customer_name" name="customer_name" 
                                   value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_email">Email:</label>
                            <input type="email" id="customer_email" name="customer_email" 
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">Phone:</label>
                            <input type="tel" id="customer_phone" name="customer_phone" 
                                   value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="rental_days">Rental Days:</label>
                            <input type="number" id="rental_days" name="rental_days" min="1" value="1" required>
                        </div>

                        <div class="price-estimate">
                            <p>Daily Rate: ₹<?php echo number_format($vehicle['daily_rate'], 2); ?></p>
                            <p>Estimated Total: <span id="total-price">₹<?php echo number_format($vehicle['daily_rate'], 2); ?></span></p>
                        </div>

                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <footer>
            <p><a href="index.php">← Back to Vehicle List</a></p>
        </footer>
    </div>

    <script>
        // Calculate total price dynamically
        const rentalDaysInput = document.getElementById('rental_days');
        const totalPriceSpan = document.getElementById('total-price');
        const dailyRate = <?php echo $vehicle['daily_rate']; ?>;

        function calculateTotal() {
            const days = parseInt(rentalDaysInput.value) || 0;
            const total = dailyRate * days;
            totalPriceSpan.textContent = '₹' + total.toFixed(2);
        }

        rentalDaysInput.addEventListener('input', calculateTotal);
    </script>
</body>
</html>
