<?php
require_once 'config.php';

// Fetch available vehicles
$vehicles_query = "SELECT * FROM vehicles WHERE status = 'available' ORDER BY type, name";
$vehicles_result = $conn->query($vehicles_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Wheels (Vehicle on Rent) - Book Your Ride</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="header-left">
                    <h1>Vehicle Rental System</h1>
                    <p>Book your perfect ride today</p>
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

        <div class="booking-section">
            <h2>Available Vehicles</h2>
            
            <?php if ($vehicles_result->num_rows > 0): ?>
                <div class="vehicles-grid">
                    <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-image">
                                <?php if (!empty($vehicle['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['image_url']); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>">
                                <?php else: ?>
                                    <div class="vehicle-icon">
                                        <span class="vehicle-type-icon"><?php echo ucfirst($vehicle['type']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($vehicle['name']); ?></h3>
                            <?php if (!empty($vehicle['car_no'])): ?>
                                <p class="vehicle-car-no"><?php echo htmlspecialchars($vehicle['car_no']); ?></p>
                            <?php endif; ?>
                            <p class="vehicle-type"><?php echo ucfirst($vehicle['type']); ?></p>
                            <p class="vehicle-price">₹<?php echo number_format($vehicle['daily_rate'], 2); ?> / day</p>
                            <div class="vehicle-status">
                                <span class="status-badge <?php echo $vehicle['status']; ?>">
                                    <?php echo ucfirst($vehicle['status']); ?>
                                </span>
                            </div>
                            <div class="vehicle-actions">
                                <a href="details.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-secondary btn-small">More Info</a>
                                <?php if ($vehicle['status'] === 'available'): ?>
                                    <?php if (isLoggedIn() && !isAdmin()): ?>
                                        <a href="book.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary btn-small">Book Now</a>
                                    <?php elseif (!isLoggedIn()): ?>
                                        <a href="login.php" class="btn btn-primary btn-small">Login to Book</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-small" disabled>Rented</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-vehicles">
                    <p>No vehicles available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
