<?php
require_once 'config.php';

// Delete any existing admin user
$conn->query("DELETE FROM users WHERE username='admin'");

$password = 'admin123';
$username = 'admin';
$role = 'admin';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $username, $hashed_password, $role);

if ($stmt->execute()) {
    echo "Admin user created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Hash: " . $hashed_password . "<br>";
    echo "<br><a href='login.php'>Go to Login</a>";
} else {
    echo "Error creating admin user: " . $conn->error;
}
?>
