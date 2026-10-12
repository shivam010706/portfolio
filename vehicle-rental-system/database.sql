-- Vehicle Rental System Database Schema
-- Database: vehicle_rental_db

CREATE DATABASE IF NOT EXISTS vehicle_rental_db;
USE vehicle_rental_db;

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('car', 'bike') NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'rented') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    rental_days INT NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'returned') DEFAULT 'active',
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@vehicle-rental.com', 'admin');

-- Insert sample vehicles (rates in INR)
INSERT INTO vehicles (name, type, daily_rate, status) VALUES
('Toyota Camry', 'car', 4150.00, 'available'),
('Honda Civic', 'car', 3735.00, 'available'),
('Ford Mustang', 'car', 6640.00, 'available'),
('Yamaha MT-15', 'bike', 2075.00, 'available'),
('Honda CBR', 'bike', 2905.00, 'available'),
('Royal Enfield', 'bike', 2490.00, 'available');
