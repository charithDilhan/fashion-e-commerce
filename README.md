# Fashion Store — Completed PHP/MySQL System

## Setup

1. Copy the project folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `fashion_db_fixed.sql`.
4. Open the project folder URL, for example: `http://localhost/fashion_web/`.
5. The application automatically supports MySQL port `3306` and falls back to `3307`.

## Demonstration logins

### Admin / Seller
- Email: `admin@fashionstore.lk`
- Password: `Admin@123`

### Customer
- Email: `customer@fashionstore.lk`
- Password: `Customer@123`

Passwords from the original database remain compatible. After a successful login, legacy SHA-1 passwords are automatically upgraded to PHP's secure password hash.

## Completed modules

- Responsive customer website
- Product search, category filter, and sorting
- Product details with missing-image fallbacks
- Customer registration, login, logout, profile, and password update
- Wishlist and shopping cart
- Stock-aware checkout using database transactions
- Customer order history and status tracking
- Contact messages
- Seller/admin registration and login
- Admin dashboard
- Product create, read, update, status toggle, and delete
- Optional product images with secure upload validation
- Admin order and payment processing
- Automatic stock restoration when an order is canceled
- Customer and message management
- Dynamic paths, so the project works even when the folder name changes
- CSRF protection, prepared SQL statements, output escaping, and session hardening

## Images

Product image uploads are optional. When an image is missing, the system uses lightweight SVG placeholders from the `assets` folder instead of showing a broken image.


## Public page image links
- Login, register, home, about, shop, contact, and product detail hero/background sections now use **real remote image URLs** (Pexels) inside the PHP/CSS files.
- You can replace those URLs anytime with your own preferred image links without changing the page structure.
- The logo is stored locally at `assets/logo.svg`.


## Admin data visibility fix
- `admin@fashionstore.lk` is treated as the **System / Super Admin** and can see/manage all sellers, products, orders, customers, messages, cart records, and wishlist records.
- Other seller accounts can only see and manage their own products and orders.
- Existing databases do not need a schema migration for this access fix.

## July 2026 integrity repair

This build validates the `user_id` and `seller_id` stored in every browser session before any page uses them. Deleted or outdated sessions are cleared automatically, preventing foreign-key failures in contact messages, cart records, wishlist records, products, and checkout.

Additional fixes include:

- Contact messages safely fall back to guest messages if a customer record was removed in another tab.
- Cart and wishlist actions re-check the authenticated database account before inserting data.
- Foreign-key exceptions are handled as readable messages instead of uncaught fatal errors.
- The cart query now loads complete product details and uses the current product price.
- Checkout revalidates the customer, stock, address type, payment method, and transaction state.
- Product category and description validation now runs on the server.
- Database exceptions are logged while customers receive safe, readable messages.

### Existing database

Do not import `fashion_db_fixed.sql` over a database whose data you want to keep, because that file rebuilds the tables. Import `fashion_db_repair.sql` once instead. It removes orphan cart/wishlist rows, detaches messages from deleted users, repairs missing product sellers, synchronizes cached prices, and normalizes product values.

After replacing the project files, simply refresh the website. Old invalid login sessions are cleared automatically; manually clearing browser cookies is not required.

### Role access repair

Regular seller accounts are restricted to their own products, orders, and profile. Store-wide messages, customer deletion, seller lists, cart totals, and wishlist totals are available only to the System Admin.
