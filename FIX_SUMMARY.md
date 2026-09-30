# Fashion Store Fix Summary

## Main fatal-error repair

The previous browser session could contain a `user_id` that no longer existed in the `users` table. The application then tried to insert that deleted ID into `message`, `cart`, and `wishlist`, causing SQLSTATE `23000 / 1452` foreign-key failures.

This build validates customer and seller session IDs against the database on every request. Invalid sessions are removed automatically before they can be used in SQL.

## Other completed fixes

- Contact messages use a real authenticated user ID only when that user still exists; otherwise the message is saved safely as a guest message.
- Cart and wishlist inserts revalidate both the account and product and handle integrity exceptions without fatal pages.
- `cart.php` now selects complete product data instead of only cart fields and stock.
- Checkout validates the current customer, stock, address type, payment method, and transaction state.
- Product category and description validation is enforced server-side.
- Raw database exception details are logged instead of being shown to customers.
- Regular sellers are restricted to their own products/orders/profile; store-wide customer and message management is System Admin only.
- A non-destructive `fashion_db_repair.sql` is included for existing databases.

## Installation

1. Replace the old project folder with this fixed project folder.
2. Keep your existing database and import `fashion_db_repair.sql` once in phpMyAdmin.
3. For a completely new database only, import `fashion_db_fixed.sql` instead.
4. Start Apache and MySQL, then refresh the website. Invalid old sessions clear automatically.

## Demonstration accounts

- System Admin: `admin@fashionstore.lk` / `Admin@123`
- Customer: `customer@fashionstore.lk` / `Customer@123`
