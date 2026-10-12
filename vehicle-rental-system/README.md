# Vehicle Rental System

A complete web-based vehicle rental booking system built with HTML, PHP, and MySQL. This application allows customers to book vehicles online and provides an admin dashboard for managing the rental fleet.

## 🎯 Features

### Customer Booking Screen (`index.php`)
- Browse available vehicles (cars and bikes)
- View vehicle details and daily rates
- Book vehicles by entering personal details
- Automatic price calculation based on rental days
- Real-time availability updates

### Admin Control Panel (`admin.php`)
- Dashboard with key metrics:
  - Total Assets count
  - Active Bookings count
  - Total Revenue
- Add new vehicles to the inventory
- View complete vehicle inventory with status
- Manage active bookings
- Process vehicle returns (updates availability)

### Database Engine (`vehicle_rental_db`)
- **Vehicles Table**: Stores vehicle inventory with name, type, daily rate, and availability status
- **Bookings Table**: Records all bookings with customer details, rental duration, and total cost
- Foreign key relationship ensures data integrity

## 🚀 Key Features

### State Management
- Booking a vehicle automatically sets its status to "Rented"
- Rented vehicles are hidden from public view
- Prevents double-bookings

### CRUD Operations
- **Create**: New bookings and vehicle additions
- **Read**: Inventory tables and booking records
- **Update**: Vehicle status (available/rented) and booking status (active/returned)

## 📋 Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, or PHP built-in server)

## 🔧 Installation

### 1. Setup Database

Import the database schema using MySQL command line or phpMyAdmin:

```bash
mysql -u root -p < database.sql
```

Or manually execute the SQL in `database.sql` through your MySQL management tool.

### 2. Configure Database Connection

Edit `config.php` if your MySQL credentials differ from defaults:

```php
$host = 'localhost';
$username = 'root';      // Your MySQL username
$password = '';          // Your MySQL password
$database = 'vehicle_rental_db';
```

### 3. Deploy to Web Server

Place all files in your web server's document root:

- **Apache**: `/var/www/html/` or `C:\xampp\htdocs\`
- **Nginx**: `/usr/share/nginx/html/`
- **PHP Built-in**: Run from project directory

### 4. Run Using PHP Built-in Server

For quick testing, use PHP's built-in server:

```bash
php -S localhost:8000
```

Then access:
- Customer Booking: `http://localhost:8000/index.php`
- Admin Panel: `http://localhost:8000/admin.php`

## 📁 Project Structure

```
vehicle-rental-system/
├── index.php          # Customer booking interface
├── admin.php          # Admin control panel
├── config.php         # Database connection configuration
├── style.css          # Styling for both interfaces
├── database.sql       # MySQL database schema
└── README.md          # This file
```

## 🎨 Usage

### For Customers

1. Open `index.php` in your browser
2. Browse available vehicles displayed in a grid
3. Select a vehicle from the dropdown
4. Enter your personal details (name, email, phone)
5. Specify rental days
6. View the estimated total price
7. Click "Book Now" to complete the booking

### For Administrators

1. Open `admin.php` in your browser
2. View dashboard statistics
3. **Add Vehicle**: Fill in the form to add new vehicles to inventory
4. **View Inventory**: See all vehicles with their current status
5. **Manage Bookings**: View active bookings and process returns
6. Click "Return" button when a vehicle is returned to make it available again

## 🔒 Security Notes

- This is a demonstration project without authentication
- In production, add:
  - Admin authentication (login system)
  - Input validation and sanitization
  - CSRF protection
  - Prepared statements (already implemented)
  - HTTPS/SSL encryption

## 🛠️ Customization

### Add More Vehicles

Edit `database.sql` and add more INSERT statements:

```sql
INSERT INTO vehicles (name, type, daily_rate, status) VALUES
('Your Vehicle Name', 'car', 75.00, 'available');
```

### Modify Styling

Edit `style.css` to customize colors, layouts, and design elements.

### Change Database Credentials

Update `config.php` with your MySQL credentials.

## 📊 Database Schema

### Vehicles Table
- `id`: Primary key (auto-increment)
- `name`: Vehicle name (string)
- `type`: Vehicle type (enum: 'car', 'bike')
- `daily_rate`: Daily rental price (decimal)
- `status`: Availability status (enum: 'available', 'rented')
- `created_at`: Timestamp of record creation

### Bookings Table
- `id`: Primary key (auto-increment)
- `vehicle_id`: Foreign key to vehicles table
- `customer_name`: Customer's full name
- `customer_email`: Customer's email address
- `customer_phone`: Customer's phone number
- `rental_days`: Number of rental days
- `total_cost`: Calculated total cost
- `booking_date`: Timestamp of booking
- `status`: Booking status (enum: 'active', 'returned')

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `config.php`
- Ensure database `vehicle_rental_db` exists

### No Vehicles Showing
- Run `database.sql` to populate sample data
- Check vehicle status in admin panel

### Booking Not Processing
- Verify vehicle is available
- Check form validation
- Review PHP error logs

## 📝 License

This project is open source and available for educational purposes.

## 🤝 Contributing

Feel free to submit issues and enhancement requests!

---

**Built with ❤️ using PHP, MySQL, HTML, and CSS**
