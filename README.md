# ArtConnect

ArtConnect is a web-based platform for ordering custom paintings and portraits. It allows customers to browse galleries, place orders for personalized artwork, and manage their accounts. Admins can manage the gallery, view orders, and handle customer interactions.

## Features

### For Customers
- **Browse Gallery**: View featured paintings and portraits in interactive carousels.
- **Order Paintings**: Select from pre-made paintings with pricing and sizes.
- **Order Custom Portraits**: Upload photos and customize portraits with options like art type, size, orientation, and number of faces.
- **Wishlist**: Add paintings to a personal wishlist.
- **Account Management**: Register, login, and manage orders through a customer panel.
- **Reviews**: Submit and view customer reviews.
- **Contact**: Send messages to the support team.

### For Admins
- **Admin Panel**: Secure login to manage the platform.
- **Gallery Management**: Upload and delete paintings and gallery photos.
- **Order Management**: View and update order statuses (pending, shipped, delivered, cancelled).
- **Customer Reviews**: View submitted reviews.
- **Contact Submissions**: View and manage contact form submissions.
- **Download Files**: Download uploaded customer photos securely.

### General
- **Responsive Design**: Built with Bootstrap for mobile and desktop compatibility.
- **Secure Authentication**: Password hashing for customer accounts, session management.
- **File Uploads**: Secure handling of image uploads with validation.
- **Database Integration**: MySQL database for storing users, orders, gallery items, etc.

## Tech Stack

- **Backend**: PHP 7+ with PDO for database interactions.
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3.
- **Database**: MySQL.
- **Server**: Apache (via XAMPP for local development).
- **Other Libraries**: Font Awesome for icons.

## Installation

### Prerequisites
- XAMPP (or similar Apache, MySQL, PHP stack).
- PHP 7.0 or higher.
- MySQL 5.7 or higher.
- A web browser.

### Steps
1. **Clone or Download the Project**:
   - Place the `artconnect` folder in your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\artconnect`).

2. **Database Setup**:
   - Start XAMPP and ensure Apache and MySQL are running.
   - Open phpMyAdmin (http://localhost/phpmyadmin).
   - Create a database named `artconnect`.
   - Import the SQL file: Go to the `sql` folder and import `setup.sql` into the `artconnect` database.
   - Note: The application uses additional tables not in `setup.sql`. You may need to create them manually or run the app to auto-create (check error logs).

3. **Configuration**:
   - Update `config/db.php` if your database credentials differ (default: host=localhost, db=artconnect, user=root, password=empty).
   - Ensure the `uploads` folder is writable (chmod 755 or 777 on Linux/Mac, or adjust permissions on Windows).

4. **Run the Application**:
   - Open your browser and navigate to `http://localhost/artconnect`.
   - Admin login: Default credentials are username: `admin`, password: `password123` (change in production).

## Database Schema

The application uses the following tables (some may need manual creation):

- `customers`: Stores customer accounts (email, name, hashed password).
- `gallery`: Stores paintings and gallery photos (id, image_path, type, price, size).
- `orders`: Stores order details (id, num_faces, art_type, art_size, etc.).
- `order_photos`: Links orders to uploaded photos.
- `reviews`: Customer reviews (customer_email, rating, comment).
- `contacts`: Contact form submissions.
- `wishlist`: Customer wishlists.
- `final_form_submissions`: Final order confirmations.

## Usage

1. **Homepage**: View featured artworks and navigate to ordering pages.
2. **Customer Registration/Login**: Create an account or log in to place orders.
3. **Order Paintings**: Browse and order from available paintings.
4. **Order Portraits**: Customize and upload photos for portraits.
5. **Admin Panel**: Log in as admin to manage content and orders.
6. **Gallery**: View uploaded gallery photos.
7. **Reviews**: Submit and read reviews.
8. **Contact**: Send inquiries.

## File Structure

```
artconnect/
├── about.php              # About page
├── admin_login.php        # Admin login
├── admin_panel.php        # Admin dashboard
├── contact.php            # Contact form
├── customer_login.php     # Customer login
├── customer_panel.php     # Customer dashboard
├── customer_register.php  # Customer registration
├── download.php           # Secure file download
├── final_form.php         # Final order form
├── gallery.php            # Gallery page
├── header.php             # Common header/navigation
├── index.php              # Homepage
├── logout.php             # Admin logout
├── order_form.php         # Portrait order form
├── painting.php           # Painting order page
├── portraits.php          # Portraits page
├── review.php             # Reviews page
├── wishlist_action.php    # Wishlist AJAX handler
├── assets/                # Static assets
│   ├── css/
│   ├── images/
│   └── js/
├── config/                # Configuration
│   └── db.php
├── sql/                   # Database setup
│   └── setup.sql
└── uploads/               # Uploaded files
```

## Security Notes

- Passwords are hashed using `password_hash()`.
- File uploads are validated for type and size.
- Sessions are used for authentication.
- SQL injection is prevented with prepared statements.
- Change default admin credentials in production.

## Contributing

1. Fork the repository.
2. Create a feature branch.
3. Make changes and test thoroughly.
4. Submit a pull request.

## License

This project is for educational purposes. Use at your own risk.

## Support

For issues or questions, check the code comments or contact the developer.
