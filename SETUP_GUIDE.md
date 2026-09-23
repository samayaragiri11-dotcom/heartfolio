# Quick Setup Guide for Heartfolio

## Step-by-Step Installation

### 1. Install XAMPP
- Download XAMPP from: https://www.apachefriends.org/
- Run the installer and complete installation
- Open XAMPP Control Panel
- Start **Apache** and **MySQL** modules

### 2. Setup the Database
1. Open your browser and go to: http://localhost/phpmyadmin
2. Click on **New** in the left sidebar
3. Enter database name: `heartfolio`
4. Click **Create**
5. Click on the `heartfolio` database
6. Click on **Import** tab
7. Choose file: `database/heartfolio.sql` from your project folder
8. Click **Go** at the bottom
9. You should see "Import has been successfully finished"

### 3. Place the Project Files
1. Navigate to: `C:\xampp\htdocs\`
2. Copy the entire `projectheartfolio` folder here
3. Your project structure should be: `C:\xampp\htdocs\projectheartfolio\`

### 4. Access the Website
- Open your browser
- Go to: http://localhost/projectheartfolio
- You should see the Heartfolio home page

### 5. Add Product Images (Important!)
The website needs product images to display properly:

1. Go to: `C:\xampp\htdocs\projectheartfolio\assets\images\`
2. Add magazine images with these exact filenames:
   - `magazine-hero.jpg` (for homepage hero)
   - `friendship-1.jpg`, `friendship-2.jpg`, etc.
   - `love-1.jpg`, `love-2.jpg`, etc.
   - `birthday-1.jpg`
   - `family-1.jpg`
   - `anniversary-1.jpg`
   - `travel-1.jpg`
   - `other-1.jpg`

**Tip**: You can use any magazine images from the internet for demonstration purposes. Just rename them to match the filenames above.

### 6. Test the Website

#### Customer Flow:
1. Browse the home page
2. Click "Shop" to view products
3. Click on any product to see details
4. Sign up for a new account
5. Add products to cart
6. Proceed to checkout
7. Place an order

#### Admin Flow:
1. Go to: http://localhost/projectheartfolio/login.php
2. Login with:
   - Email: `admin@heartfolio.com`
   - Password: `password123`
3. You'll be redirected to the admin dashboard
4. Try adding a new product
5. View orders and inventory
6. Check sales records

## Troubleshooting

### Problem: "Connection failed" error
**Solution**: 
- Make sure MySQL is running in XAMPP
- Check that database name is exactly `heartfolio`

### Problem: Images not showing
**Solution**:
- Verify images are in `assets/images/` folder
- Check filenames match exactly (case-sensitive)
- Try clearing browser cache

### Problem: White screen or PHP errors
**Solution**:
- Make sure Apache is running in XAMPP
- Check PHP error display is enabled
- Verify file permissions

### Problem: Can't access admin panel
**Solution**:
- Make sure you're logged in with admin credentials
- Check that email is exactly: `admin@heartfolio.com`

## Project Demonstration Tips

For your BCA project defense:

1. **Start with the home page** - Show the hero section and featured products
2. **Demonstrate customer flow** - Show signup, shopping, and checkout
3. **Show admin panel** - Demonstrate product management and order tracking
4. **Explain the database** - Show the tables in phpMyAdmin
5. **Highlight features** - Mention the warm color theme, responsive design, and admin functionality

## Quick Reference

- **Website URL**: http://localhost/projectheartfolio
- **Admin URL**: http://localhost/projectheartfolio/admin
- **phpMyAdmin**: http://localhost/phpmyadmin
- **Admin Email**: admin@heartfolio.com
- **Admin Password**: password123

## Important Notes

- This is a demonstration project for educational purposes
- Images need to be added manually
- Password hashing is implemented for security
- Session management handles user authentication
- The website is responsive for mobile, tablet, and desktop

## Support

If you encounter any issues:
1. Check XAMPP services are running
2. Verify database is imported correctly
3. Ensure project files are in the correct location
4. Check browser console for JavaScript errors

Good luck with your BCA project presentation!
