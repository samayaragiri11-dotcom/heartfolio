# Heartfolio

An online shop for personalized magazines. Customers browse designs, upload their own photos and words, and order. The shop owner runs everything from an admin panel. Built with PHP and MySQL on XAMPP.

## Set up (5 minutes)

1. Copy the project into `C:\xampp\htdocs\` (for example `C:\xampp\htdocs\projectheartfolio`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/projectheartfolio/setup.php` once.
   It creates the database and tables, adds the 12 magazines, and creates an admin account. It never deletes existing accounts or orders, and it is safe to run again.
4. Log in at `http://localhost/projectheartfolio/login.php`:
   - Email: `admin@heartfolio.com`
   - Password: `password123` (only if setup created the account; change it under **My password**)

If MySQL uses a different user or password, edit `config.php`.

## What customers can do

- Browse the shop by occasion, search, sort and filter to in-stock items
- View a magazine with its photos, specs, stock level and reviews
- **Customize** any magazine: a cover photo, up to 12 page photos, a title, names and a message, with a live cover preview
- Add to cart as a guest; the cart is saved in the database and follows them after logging in
- Check out with delivery details and Cash on Delivery, eSewa or Khalti (recorded on the order; online payment isn't connected)
- Track orders with a status timeline, cancel while pending, and review magazines after delivery
- Manage their details and password; contact the shop; request a password reset

## What the admin can do

| Page | What it's for |
|---|---|
| Dashboard | Revenue, orders, customers, 14-day revenue chart, latest orders, low stock, best sellers |
| Orders | Filter by status, date or customer; open an order to see customer photos, print a packing slip and move it through Pending > Processing > Shipped > Delivered |
| Products | Add, edit, hide, feature or delete products, with cover and gallery image uploads |
| Categories | Add, rename, reorder and delete occasions |
| Inventory | See low and sold-out stock; restock or set exact amounts |
| Sales reports | Revenue, orders, average order, sales by product, category and payment method, CSV export |
| Customers | Order history and spend per customer; set a temporary password; grant admin access |
| Messages | Contact form messages and password reset requests |
| Reviews | Remove reviews |

Stock goes down when an order is placed and back up if it is cancelled. Prices are always read from the database at checkout, never from the browser.

## Project layout

```
config.php              database settings
db.php                  database connection
setup.php               one-click install / upgrade
index.php, shop.php, templates.php, product-details.php, customize.php,
cart.php, checkout.php, order.php, account.php, contact.php,
login.php, signup.php, forgot-password.php     customer pages
cart-action.php, customize-action.php, order-action.php,
review-action.php, login_process.php, signup-process.php   form handlers
photo.php               shows a customer's photo only to them or an admin
admin/                  admin panel (admin.css, partials/layout.php, one file per page)
assets/includes/        bootstrap, helpers, cart and order logic, header, footer
assets/css/style.css    storefront styles
assets/js/main.js       storefront script (menu, add to cart, quantity, toasts)
assets/images/          the product images that ship with the site
uploads/                created by setup: admin product images and customer photos (not in git)
```

## Database tables

`users`, `categories`, `products`, `product_images`, `cart_items`, `custom_photos`, `orders`, `order_items`, `order_status_history`, `reviews`, `messages`. The full definitions are in `setup.php`.

## Security notes

- Passwords are hashed with `password_hash`; repeated failed logins are slowed down.
- Every query uses prepared statements.
- Every form has a CSRF token.
- Admin pages check the role in the database on every request.
- Uploads are checked by their real file type, renamed randomly, and stored where scripts can't run. Customer photos can only be opened through `photo.php` by their owner or an admin.

## Limits

- XAMPP can't send email, so password resets go to the admin's Messages page instead of an emailed link.
- eSewa and Khalti are recorded as the chosen method; no payment gateway is connected.
