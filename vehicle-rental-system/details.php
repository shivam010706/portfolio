<?php
require_once 'config.php';

$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vehicle_id === 0) {
    header('Location: index.php');
    exit();
}

// Get vehicle details
$vehicle_query = "SELECT * FROM vehicles WHERE id = ?";
$stmt = $conn->prepare($vehicle_query);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle_result = $stmt->get_result();

if ($vehicle_result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$vehicle = $vehicle_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vehicle['name']); ?> - Elite Wheels (Vehicle on Rent)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="header-left">
                    <h1>Vehicle Details</h1>
                    <p>Elite Wheels (Vehicle on Rent)</p>
                </div>
                <div class="header-right">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-info">
                            <span><?php echo htmlspecialchars(!empty($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['username']); ?></span>
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

        <div class="vehicle-details">
            <div class="vehicle-detail-card">
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
                <div class="vehicle-info">
                    <?php if (!empty($vehicle['car_no'])): ?>
                        <p><strong>Car Number:</strong> <?php echo htmlspecialchars($vehicle['car_no']); ?></p>
                    <?php endif; ?>
                    <p><strong>Type:</strong> <?php echo ucfirst($vehicle['type']); ?></p>
                    <p><strong>Daily Rate:</strong> ₹<?php echo number_format($vehicle['daily_rate'], 2); ?> / day</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo $vehicle['status']; ?>">
                            <?php echo ucfirst($vehicle['status']); ?>
                        </span>
                    </p>
                    <?php if (!empty($vehicle['car_info'])): ?>
                        <p><strong>Additional Info:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($vehicle['car_info'])); ?></p>
                    <?php endif; ?>
                    <p><strong>Added on:</strong> <?php echo date('M d, Y', strtotime($vehicle['created_at'])); ?></p>
                </div>
                
                <?php if ($vehicle['status'] === 'available'): ?>
                    <div class="vehicle-actions">
                        <?php if (isLoggedIn() && !isAdmin()): ?>
                            <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary">Book Now</a>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-primary">Login to Book</a>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-secondary">Back to List</a>
                    </div>
                <?php else: ?>
                    <div class="vehicle-actions">
                        <a href="index.php" class="btn btn-secondary">Back to List</a>
                    </div>
                    <p class="unavailable-message">This vehicle is currently rented and not available for booking.</p>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <p><a href="index.php">← Back to Vehicle List</a></p>
        </footer>
    </div>
</body>
</html>
