# Online Classifieds Marketplace

A clean, responsive, and secure Web Development project built with **procedural PHP**, **MySQL**, and **Bootstrap 5**. Designed specifically for college lab demonstrations and viva presentations.

---

## 🌟 Key Features

1. **Homepage Showcase:** Hero banner with live search, dynamic category pills, and recent listings.
2. **Category Browsing & Filtering:** Keyword search, category selector, price range filter ($ min/max), and sorting (newest, oldest, price low/high).
3. **Item Details & Seller Info:** Individual listing pages displaying photos, status, detailed descriptions, and seller contact options (direct call & pre-filled email).
4. **Seller Dashboard:** Overview statistics (Total ads, active ads, sold count, total active inventory value) and a management table.
5. **Full CRUD Operations:** Authenticated users can create, edit, update status (Active, Sold, Archived), and delete listings.
6. **Secure Authentication:** User registration and login utilizing Bcrypt (`password_hash` and `password_verify`).
7. **Safe Image Uploads:** Multi-layer verification including file size limit (3MB), server-side MIME type detection (`finfo`), allowed extension whitelist (`jpg`, `jpeg`, `png`, `webp`), and randomized filenames.
8. **Security Protections:**
   - **SQL Injection Prevention:** 100% PDO prepared statements with bound parameters.
   - **XSS Mitigation:** HTML entity escaping via custom `e()` helper function.
   - **CSRF Defense:** Cryptographic session tokens validated on all state-changing forms.
   - **Session Security:** Session fixation defense with `session_regenerate_id(true)` upon login.

---

## 📂 Project Directory Structure

```text
online-classifieds/
├── index.php                 # Homepage with live search, categories & featured ads
├── config/
│   └── database.php          # Central PDO database connection configuration
├── includes/
│   ├── header.php            # Common HTML header, Bootstrap navbar & alerts
│   ├── footer.php            # Common footer, meta notes & script bundles
│   └── functions.php         # Procedural security, auth, CSRF, flash & upload helpers
├── auth/
│   ├── register.php          # User registration form with validation & password hashing
│   ├── login.php             # User login with password_verify & session regeneration
│   └── logout.php            # Safe session destruction and logout redirect
├── listings/
│   ├── index.php             # Search, category filter, price range & listing catalog
│   ├── view.php              # Full listing view, seller card & related items
│   ├── create.php            # Listing creation with client preview & image upload
│   ├── edit.php              # Listing update with authorization & status switch
│   └── delete.php            # Secure POST-only deletion handler with CSRF verification
├── dashboard/
│   └── index.php             # Seller dashboard with stats and ad management table
├── assets/
│   ├── css/
│   │   └── style.css         # Custom modern marketplace styles
│   └── js/
│       └── main.js           # Client-side image preview and alert auto-dismissal
├── uploads/
│   └── listings/             # Uploaded user images and pre-installed sample SVGs
├── database/
│   ├── schema.sql            # Table definitions (users, categories, listings)
│   └── seed.sql              # Default categories, demo users, and sample ads
├── .gitignore                # Git exclusions (uploads, sensitive configs, IDE files)
└── README.md                 # Project documentation & viva guide
```

---

## 🚀 Setup and Installation Guide

### Prerequisites
- **XAMPP** installed on Windows.
- **Apache** running via XAMPP Control Panel (default ports 80 and 443).
- **MySQL / MariaDB** database server running.

### Database Setup
1. Open your MySQL client (or phpMyAdmin at `http://localhost/phpmyadmin/`).
2. Run the SQL statements in `database/schema.sql` to create the `online_classifieds` database and its tables.
3. Run the SQL statements in `database/seed.sql` to populate default categories, demo users, and sample listings.

### Connecting the Project
Open `config/database.php` to verify your database credentials:
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306); // Or your active MySQL port (e.g. 3307 or 3308)
define('DB_NAME', 'online_classifieds');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL password if configured
```

### Accessing the Website
Open your browser and navigate to:
```text
http://localhost/online-classifieds/
```

---

## 🔑 Demo Login Credentials

For quick testing and examiner demonstration:
- **Email:** `alex@example.com`
- **Password:** `password123`
*(A secondary demo account `sarah@example.com` with password `password123` is also available).*

---

## 🎓 College Viva & Interview Preparation

Be prepared to answer these common questions about your code:

### 1. Why use PDO instead of MySQLi or mysql_*?
> **Answer:** PDO (PHP Data Objects) provides a unified, consistent database abstraction interface. It supports prepared statements, strong exception handling (`ERRMODE_EXCEPTION`), and protects against SQL injection when used with parameter binding.

### 2. How is SQL Injection prevented?
> **Answer:** All user input (search queries, prices, IDs, login forms) is passed through PDO prepared statements with parameter placeholders (e.g., `:id`, `:email`) or bound values, never concatenated directly into the raw SQL string.

### 3. How does password hashing work?
> **Answer:** We never store plaintext passwords. We use PHP's built-in `password_hash($password, PASSWORD_DEFAULT)` which applies the secure **Bcrypt** algorithm with an automatically generated cryptographic salt. When logging in, we verify the password with `password_verify($password, $hash)`.

### 4. What is CSRF and how is it mitigated?
> **Answer:** Cross-Site Request Forgery (CSRF) tricks a logged-in user's browser into executing unauthorized actions. We generate a cryptographically random token (`bin2hex(random_bytes(32))`), store it in the session, embed it as a hidden input (`csrf_token`), and verify it with `hash_equals()` on every POST request before performing changes.

### 5. How are file uploads kept secure?
> **Answer:** We check upload error codes, enforce a 3MB size limit, inspect the real MIME type using `finfo_file()` rather than trusting client headers, whitelist allowed extensions (`.jpg`, `.jpeg`, `.png`, `.webp`), and rename the file to a random hexadecimal string to prevent directory traversal or file execution attacks.
