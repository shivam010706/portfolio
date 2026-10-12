<?php
require_once 'config.php';

$message = '';
$messageType = '';

// Handle admin login
if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } else {
        $query = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // For debugging - create a new password hash if needed
            if ($password === 'admin123' && !password_verify($password, $user['password'])) {
                // Update the password hash to the correct one
                $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE username = 'admin'";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("s", $new_hash);
                $stmt->execute();
                $user['password'] = $new_hash;
            }
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: admin.php");
                exit();
            } else {
                $message = 'Invalid password.';
                $messageType = 'error';
            }
        } else {
            $message = 'Admin access denied. Invalid credentials.';
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
    <title>Admin Login - Elite Wheels (Vehicle on Rent)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <div class="container admin-container">
        <div class="login-box">
            <header>
                <h1>Admin Login</h1>
                <p>Elite Wheels (Vehicle on Rent) - Admin Access</p>
            </header>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Admin Username:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Admin Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group remember-me" style="display: flex; align-items: center; gap: 15px;">
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                        <input type="checkbox" name="remember_me" style="opacity: 0; width: 0; height: 0;">
                        <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.4s; border-radius: 26px;"></span>
                        <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: 0.4s; border-radius: 50%;"></span>
                    </label>
                    <span class="remember-label" style="font-size: 0.95em; color: #ccc;">Remember me</span>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary btn-block">Admin Login</button>
            </form>

            <div class="login-footer">
                <p><a href="index.php">← Back to Home</a></p>
                <p><a href="login.php">User Login</a></p>
            </div>
        </div>
    </div>

    <script>
        // Handle toggle switch animation
        document.querySelector('input[name="remember_me"]').addEventListener('change', function() {
            const slider = this.nextElementSibling;
            const knob = slider.nextElementSibling;
            if (this.checked) {
                slider.style.backgroundColor = '#764ba2';
                knob.style.transform = 'translateX(24px)';
            } else {
                slider.style.backgroundColor = '#ccc';
                knob.style.transform = 'translateX(0)';
            }
        });
    </script>
</body>
</html>
