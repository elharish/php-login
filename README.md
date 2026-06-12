# AuthPortal — PHP Login & Register System

A clean, modern PHP authentication system featuring a dark glassmorphism UI with bcrypt password hashing.

## Features
- **Register** with full name, username, email & password
- **Login** via email or username
- **Secure** — bcrypt password hashing, prepared statements (SQL injection safe)
- **Dashboard** — displays user info after login
- Password strength meter, client-side validation, password visibility toggle
- Responsive dark UI with animated background

## Project Structure
```
phplogin/
├── index.php           ← Login + Register page
├── dashboard.php       ← Post-login dashboard
├── logout.php          ← Session destroyer
├── setup.sql           ← Database schema
├── config/
│   └── database.php    ← DB credentials (edit this!)
└── assets/
    ├── css/style.css
    └── js/auth.js
```

## Setup (XAMPP / Laragon / WAMP)

### 1. Place files
Copy this folder into your web server's root directory:
- **XAMPP** → `C:\xampp\htdocs\phplogin\`
- **Laragon** → `C:\laragon\www\phplogin\`

### 2. Create the database
Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and run `setup.sql`, or via CLI:
```bash
mysql -u root -p < setup.sql
```

### 3. Configure credentials
Edit `config/database.php`:
```php
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password
```

### 4. Open in browser
```
http://localhost/phplogin/
```

## Requirements
- PHP 7.4+ (uses `password_hash` / `password_verify`)
- MySQL 5.7+ / MariaDB 10.3+
- A local web server (XAMPP, Laragon, WAMP, or `php -S localhost:8000`)
