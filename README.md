# Heartfolio - Custom Magazine Business

A complete e-commerce website for Heartfolio, a custom magazine business that sells personalized magazines for various occasions including friendship, birthday, love & couple, family, anniversary, travel, and other special occasions.

## Features

### Customer Features
- **Home Page**: Hero section with featured products and feature boxes
- **Shop Page**: Product listing with category filters and sorting options
- **Product Details**: Detailed product view with image gallery and quantity selector
- **Templates Page**: Browse available magazine templates
- **Shopping Cart**: Add/remove items, update quantities
- **Checkout**: Complete order with delivery information and payment options
- **Order Confirmation**: View order details after successful purchase
- **Customer Account**: Profile management and order history
- **User Authentication**: Login and signup functionality

### Admin Features
- **Dashboard**: Overview of total products, orders, customers, and sales
- **Product Management**: Add, edit, delete products with image upload
- **Order Management**: View and update order statuses
- **Inventory Management**: Track stock levels and update inventory
- **Sales Records**: View sales analytics and reports

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache)

## Color Theme

- Cream: #FAF5ED
- Warm Beige: #F1E6D6
- Sand: #DCC7AD
- Light Taupe: #B8A895
- Dark Brown: #3F352D
- Soft Pink: #D98291

## Installation Instructions

### Prerequisites
- XAMPP (or any PHP/MySQL server)
- Web browser (Chrome, Firefox, etc.)

### Step 1: Install XAMPP
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `heartfolio`
3. Import the SQL file from `database/heartfolio.sql`
4. Verify that the tables are created successfully

### Step 3: Configure Project
1. Copy the `projectheartfolio` folder to `C:\xampp\htdocs\`
2. The project should be accessible at `http://localhost/projectheartfolio`

### Step 4: Update Database Configuration (if needed)
If your MySQL credentials are different from default:
- Open `assets/includes/db.php`
- Update the following variables:
  ```php
  $host = 'localhost';
  $username = 'root';  // Your MySQL username
  $password = '';      // Your MySQL password
  $database = 'heartfolio';
  ```

## Default Admin Credentials

- **Email**: admin@heartfolio.com
- **Password**: password123

**Note**: For security, change the default admin password after first login.

## Project Structure

```
projectheartfolio/
├── admin/
│   ├── index.php              # Admin Dashboard
│   ├── products.php           # Product Management
│   ├── add-product.php        # Add New Product
│   ├── orders.php             # Order Management
│   ├── inventory.php          # Inventory Management
│   ├── sales.php              # Sales Records
│   ├── admin.css              # Admin-specific styles
│   └── add-product-process.php # Product form processing
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # Main JavaScript file
│   ├── images/                # Product images (add your images here)
│   ├── includes/
│   │   ├── navbar.php        # Navigation bar
│   │   ├── footer.php        # Footer
│   │   └── db.php            # Database connection
│   └── fonts/                # Custom fonts (if any)
├── database/
│   └── heartfolio.sql        # Database schema
├── pages/
│   └── (additional pages if needed)
├── index.php                 # Home page
├── login.php                 # Login page
├── signup.php                # Sign up page
├── shop.php                  # Shop/Products page
├── product-details.php       # Product details page
├── templates.php             # Templates page
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout page
├── order-confirmation.php    # Order confirmation
├── account.php               # Customer account
├── contact.php               # Contact page
├── login-process.php         # Login form processing
├── signup-process.php        # Signup form processing
├── place-order.php           # Order placement
├── logout.php                # Logout functionality
└── README.md                 # This file
```

## Adding Product Images

1. Place your product images in the `assets/images/` folder
2. Recommended image size: 800x800 pixels
3. Use the following naming convention:
   - friendship-1.jpg, friendship-2.jpg, etc.
   - love-1.jpg, love-2.jpg, etc.
   - birthday-1.jpg, etc.
4. Update the database or use the admin panel to add products with image paths

## Usage

### For Customers
1. Visit `http://localhost/projectheartfolio`
2. Browse products on the Shop page
3. View product details and add to cart
4. Proceed to checkout
5. Complete order with delivery information
6. View order history in account section

### For Admin
1. Login at `http://localhost/projectheartfolio/login.php` with admin credentials
2. Access admin dashboard at `http://localhost/projectheartfolio/admin/`
3. Manage products, orders, inventory, and view sales records

## Customization

### Changing Prices
- Update prices in the database via phpMyAdmin
- Or use the Admin Product Management panel

### Adding New Categories
- Update the category dropdown in `admin/add-product.php`
- Update the category filter in `shop.php`

### Modifying Color Theme
- Edit the CSS variables in `assets/css/style.css`:
  ```css
  :root {
      --cream: #FAF5ED;
      --warm-beige: #F1E6D6;
      --sand: #DCC7AD;
      --light-taupe: #B8A895;
      --dark-brown: #3F352D;
      --soft-pink: #D98291;
  }
  ```

## Important Notes

- This project is designed for educational purposes (BCA project)
- No magazine customization editor is included (as per requirements)
- Customers can only browse and purchase available magazine templates
- Admin manages all business operations through the dashboard
- The website uses session-based authentication
- Images need to be added manually to the `assets/images/` folder

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Check database credentials in `assets/includes/db.php`
- Verify the database name is `heartfolio`

### Images Not Displaying
- Check that images are in the correct folder: `assets/images/`
- Verify image file names match the database entries
- Check file permissions

### PHP Errors
- Ensure PHP is enabled in XAMPP
- Check PHP error logs in XAMPP
- Verify file permissions

## Future Enhancements

- Email notifications for orders
- Payment gateway integration (eSewa, Khalti)
- Advanced search functionality
- Product reviews and ratings
- Wishlist feature
- Discount coupon system
- Advanced analytics dashboard

## License

This project is created for educational purposes.

## Credits

Heartfolio - Custom Magazine Business
BCA Project 2024
