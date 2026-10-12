<?php
require_once 'config.php';

// Check if user is logged in and is admin
requireLogin();
requireAdmin();

$message = '';
$messageType = '';

// Handle add vehicle
if (isset($_POST['add_vehicle'])) {
    $name = trim($_POST['vehicle_name']);
    $car_no = trim($_POST['car_no'] ?? '');
    $car_info = trim($_POST['car_info'] ?? '');
    $type = $_POST['vehicle_type'];
    $daily_rate = floatval($_POST['daily_rate']);
    $image_url = null;
    
    // Handle image upload
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = 'vehicle_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = __DIR__ . '/uploads/' . $file_name;
            
            // Ensure uploads directory exists
            if (!is_dir(__DIR__ . '/uploads')) {
                mkdir(__DIR__ . '/uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/' . $file_name;
            } else {
                $message = 'Error uploading image. Check folder permissions.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp';
            $messageType = 'error';
        }
    }
    
    if (empty($name) || empty($type) || $daily_rate <= 0) {
        $message = 'Please fill in all fields correctly.';
        $messageType = 'error';
    } else {
        $query = "INSERT INTO vehicles (name, car_no, car_info, type, daily_rate, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssds", $name, $car_no, $car_info, $type, $daily_rate, $image_url);
        
        if ($stmt->execute()) {
            $message = 'Vehicle added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding vehicle.';
            $messageType = 'error';
        }
    }
}

// Handle delete vehicle
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = intval($_POST['vehicle_id']);
    
    // Check if vehicle has active bookings
    $check_query = "SELECT COUNT(*) as count FROM bookings WHERE vehicle_id = ? AND status = 'active'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_bookings = $result->fetch_assoc()['count'];
    
    if ($active_bookings > 0) {
        $message = 'Cannot delete vehicle with active bookings.';
        $messageType = 'error';
    } else {
        $delete_query = "DELETE FROM vehicles WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $vehicle_id);
        
        if ($stmt->execute()) {
            $message = 'Vehicle deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting vehicle.';
            $messageType = 'error';
        }
    }
}

// Handle edit vehicle - populate form
$edit_mode = false;
$edit_vehicle = null;
if (isset($_POST['edit_vehicle'])) {
    $vehicle_id = intval($_POST['vehicle_id']);
    $edit_query = "SELECT * FROM vehicles WHERE id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $edit_vehicle = $result->fetch_assoc();
        $edit_mode = true;
    }
}

// Handle update vehicle
if (isset($_POST['update_vehicle'])) {
    $vehicle_id = intval($_POST['vehicle_id']);
    $name = trim($_POST['vehicle_name']);
    $car_no = trim($_POST['car_no'] ?? '');
    $car_info = trim($_POST['car_info'] ?? '');
    $type = $_POST['vehicle_type'];
    $daily_rate = floatval($_POST['daily_rate']);
    $image_url = $_POST['existing_image'] ?? null;
    
    // Handle image upload
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = 'vehicle_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = __DIR__ . '/uploads/' . $file_name;
            
            // Ensure uploads directory exists
            if (!is_dir(__DIR__ . '/uploads')) {
                mkdir(__DIR__ . '/uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($image_url && file_exists(__DIR__ . '/' . $image_url)) {
                    unlink(__DIR__ . '/' . $image_url);
                }
                $image_url = 'uploads/' . $file_name;
            } else {
                $message = 'Error uploading image. Check folder permissions.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp';
            $messageType = 'error';
        }
    }
    
    if (empty($name) || empty($type) || $daily_rate <= 0) {
        $message = 'Please fill in all fields correctly.';
        $messageType = 'error';
    } else {
        $update_query = "UPDATE vehicles SET name = ?, car_no = ?, car_info = ?, type = ?, daily_rate = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssdsi", $name, $car_no, $car_info, $type, $daily_rate, $image_url, $vehicle_id);
        
        if ($stmt->execute()) {
            $message = 'Vehicle updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating vehicle.';
            $messageType = 'error';
        }
    }
}

// Handle return vehicle
if (isset($_POST['return_vehicle'])) {
    $booking_id = intval($_POST['booking_id']);

    $query = "SELECT vehicle_id FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $vehicle_id = $booking['vehicle_id'];

        // Update booking status
        $update_booking = "UPDATE bookings SET status = 'returned' WHERE id = ?";
        $stmt = $conn->prepare($update_booking);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Update vehicle status
        $update_vehicle = "UPDATE vehicles SET status = 'available' WHERE id = ?";
        $stmt = $conn->prepare($update_vehicle);
        $stmt->bind_param("i", $vehicle_id);

        if ($stmt->execute()) {
            $message = 'Vehicle returned successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error returning vehicle.';
            $messageType = 'error';
        }
    } else {
        $message = 'Booking not found.';
        $messageType = 'error';
    }
}

// Handle accept booking
if (isset($_POST['accept_booking'])) {
    $booking_id = intval($_POST['booking_id']);

    $query = "SELECT vehicle_id FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $vehicle_id = $booking['vehicle_id'];

        // Update booking status
        $update_booking = "UPDATE bookings SET status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($update_booking);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Update vehicle status
        $update_vehicle = "UPDATE vehicles SET status = 'rented' WHERE id = ?";
        $stmt = $conn->prepare($update_vehicle);
        $stmt->bind_param("i", $vehicle_id);

        if ($stmt->execute()) {
            $message = 'Booking accepted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error accepting booking.';
            $messageType = 'error';
        }
    } else {
        $message = 'Booking not found.';
        $messageType = 'error';
    }
}

// Handle decline booking
if (isset($_POST['decline_booking'])) {
    $booking_id = intval($_POST['booking_id']);

    // Update booking status
    $update_booking = "UPDATE bookings SET status = 'declined' WHERE id = ?";
    $stmt = $conn->prepare($update_booking);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        $message = 'Booking declined successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error declining booking.';
        $messageType = 'error';
    }
}

// Get dashboard statistics
$total_assets = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'];
$active_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'active'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_cost) as total FROM bookings")->fetch_assoc()['total'] ?? 0;

// Get all vehicles
$vehicles_query = "SELECT * FROM vehicles ORDER BY status, type, name";
$vehicles_result = $conn->query($vehicles_query);

// Get active bookings
$bookings_query = "SELECT b.*, v.name as vehicle_name, v.type as vehicle_type
                   FROM bookings b
                   JOIN vehicles v ON b.vehicle_id = v.id
                   WHERE b.status = 'active'
                   ORDER BY b.booking_date DESC";
$bookings_result = $conn->query($bookings_query);

// Get pending bookings
$pending_bookings_query = "SELECT b.*, v.name as vehicle_name, v.type as vehicle_type
                           FROM bookings b
                           JOIN vehicles v ON b.vehicle_id = v.id
                           WHERE b.status = 'pending'
                           ORDER BY b.booking_date DESC";
$pending_bookings_result = $conn->query($pending_bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Elite Wheels (Vehicle on Rent)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <div class="container admin-container">
        <header>
            <h1>Admin Control Panel</h1>
            <p>Manage Elite Wheels (Vehicle on Rent)</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Statistics -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">Vehicle</div>
                <div class="stat-info">
                    <h3>Total Assets</h3>
                    <p class="stat-value"><?php echo $total_assets; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">Booking</div>
                <div class="stat-info">
                    <h3>Active Bookings</h3>
                    <p class="stat-value"><?php echo $active_bookings; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">Revenue</div>
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <p class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Add/Edit Vehicle Form -->
        <div class="admin-section">
            <h2><?php echo $edit_mode ? 'Edit Vehicle' : 'Add New Vehicle'; ?></h2>
            <form method="POST" action="" class="admin-form" enctype="multipart/form-data">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="vehicle_id" value="<?php echo $edit_vehicle['id']; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_vehicle['image_url'] ?? ''); ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="vehicle_name">Vehicle Name:</label>
                        <input type="text" id="vehicle_name" name="vehicle_name" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_vehicle['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="car_no">Car Number:</label>
                        <input type="text" id="car_no" name="car_no" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_vehicle['car_no'] ?? '') : ''; ?>" placeholder="e.g., MH-12-AB-1234">
                    </div>
                    <div class="form-group">
                        <label for="vehicle_type">Type:</label>
                        <select id="vehicle_type" name="vehicle_type" required>
                            <option value="car" <?php echo $edit_mode && $edit_vehicle['type'] === 'car' ? 'selected' : ''; ?>>Car</option>
                            <option value="bike" <?php echo $edit_mode && $edit_vehicle['type'] === 'bike' ? 'selected' : ''; ?>>Bike</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="daily_rate">Daily Rate (₹):</label>
                        <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_vehicle['daily_rate']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="car_info">Car Info:</label>
                    <textarea id="car_info" name="car_info" rows="3" placeholder="Additional details about the vehicle..."><?php echo $edit_mode ? htmlspecialchars($edit_vehicle['car_info'] ?? '') : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="vehicle_image">Vehicle Image:</label>
                    <input type="file" id="vehicle_image" name="vehicle_image" accept="image/*">
                    <?php if ($edit_mode && !empty($edit_vehicle['image_url'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($edit_vehicle['image_url']); ?>" alt="Current vehicle image" style="max-width: 200px; margin-top: 10px;">
                            <p><small>Current image (leave blank to keep)</small></p>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" name="<?php echo $edit_mode ? 'update_vehicle' : 'add_vehicle'; ?>" class="btn btn-primary">
                    <?php echo $edit_mode ? 'Update Vehicle' : 'Add Vehicle'; ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="admin.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Vehicle Inventory -->
        <div class="admin-section">
            <h2>Vehicle Inventory</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Car No</th>
                            <th>Type</th>
                            <th>Daily Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vehicles_result->num_rows > 0): ?>
                            <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($vehicle['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($vehicle['image_url']); ?>" alt="<?php echo htmlspecialchars($vehicle['name']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <span style="font-size: 0.9em; font-weight: 500;"><?php echo ucfirst($vehicle['type']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $vehicle['id']; ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['name']); ?></td>
                                    <td><?php echo !empty($vehicle['car_no']) ? htmlspecialchars($vehicle['car_no']) : '-'; ?></td>
                                    <td><?php echo ucfirst($vehicle['type']); ?></td>
                                    <td>₹<?php echo number_format($vehicle['daily_rate'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $vehicle['status']; ?>">
                                            <?php echo ucfirst($vehicle['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                            <button type="submit" name="edit_vehicle" class="btn btn-small btn-secondary">Edit</button>
                                        </form>
                                        <form method="POST" action="" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                            <button type="submit" name="delete_vehicle" class="btn btn-small btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No vehicles found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Bookings -->
        <div class="admin-section">
            <h2>Pending Bookings</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Days</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pending_bookings_result->num_rows > 0): ?>
                            <?php while ($booking = $pending_bookings_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['vehicle_name']); ?>
                                        <small>(<?php echo ucfirst($booking['vehicle_type']); ?>)</small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                                    </td>
                                    <td><?php echo $booking['rental_days']; ?></td>
                                    <td>₹<?php echo number_format($booking['total_cost'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="accept_booking" class="btn btn-small btn-success">Accept</button>
                                        </form>
                                        <form method="POST" action="" class="inline-form" onsubmit="return confirm('Are you sure you want to decline this booking?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="decline_booking" class="btn btn-small btn-danger">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No pending bookings.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Bookings -->
        <div class="admin-section">
            <h2>Active Bookings</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Days</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings_result->num_rows > 0): ?>
                            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['vehicle_name']); ?>
                                        <small>(<?php echo ucfirst($booking['vehicle_type']); ?>)</small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($booking['customer_phone']); ?></small>
                                    </td>
                                    <td><?php echo $booking['rental_days']; ?></td>
                                    <td>₹<?php echo number_format($booking['total_cost'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="return_vehicle" class="btn btn-small btn-success">
                                                Return
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No active bookings.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer>
            <p><a href="index.php">← Back to Booking Page</a> | <a href="logout.php">Logout</a></p>
        </footer>
    </div>
</body>
</html>
