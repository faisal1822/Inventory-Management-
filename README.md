# StockMaster Pro - Inventory & Stock Management System

A robust, enterprise-grade Inventory & Stock Management System developed with **Laravel 11**, **PHP 8.2+**, **MySQL**, **Blade**, **Bootstrap 5**, and **JavaScript**. Designed for small to mid-sized retail shops, wholesale distributors, warehouses, and commercial businesses.

---

## 1. Project Overview

**StockMaster Pro** provides real-time stock control, purchase order processing, POS / sales invoicing, stock adjustment audit trails, inventory valuation reports, and multi-tier role-based access control (Admin & Staff).

---

## 2. Key Features

- **Authentication & Role-Based Access Control (RBAC):**
  - **Admin Role:** Full dashboard, product catalog CRUD, category & supplier management, purchase orders, sales, stock adjustments, financial and valuation reports, staff account management, and system settings.
  - **Staff Role:** Streamlined access to POS / sales invoicing, product stock inquiries, customer receipts, and operational sales reports. Admin settings and staff management are strictly protected via authorization middleware.
  - **Secure Provisioning:** Public registration is restricted to prevent unauthorized administrative escalation. Initial admin accounts are provisioned via database seeder or the dedicated Artisan CLI command `php artisan make:admin`.
- **Dynamic Business Dashboard:**
  - Real-time aggregate statistics: Total Products, Categories, Active Suppliers, Total Warehouse Units, Today's Sales Revenue, Completed Orders Count, Procurement Value, and Low-Stock Warnings.
  - Interactive **Chart.js** visualizations: 7-day sales revenue trend and category stock distribution doughnut chart.
  - Instant reorder alerts for products meeting or dropping below their minimum threshold.
- **Product & Catalog Management:**
  - Full CRUD operations with auto-generating unique SKU codes and optional EAN/UPC barcode support.
  - Image upload with client-side preview, validation (JPG, PNG, WEBP max 2MB), and storage management.
  - Stock search by name, SKU, or barcode; filtering by category and stock availability (`In Stock`, `Low Stock`, `Out of Stock`).
  - Relational protection: Prevents accidental deletion of categories or suppliers with active product records or historical transaction data.
- **Purchase Management (Stock Intake):**
  - Multi-item purchase order entry with dynamic line additions/removals and automatic real-time total recalculations.
  - Database transactions with atomic stock increments.
  - Automatic updates to product cost price and immediate logging to the traceable stock movement history.
  - Printable purchase order receipts with vendor details.
- **Sales & Point of Sale (POS):**
  - Fast counter checkout with dynamic item addition, unit price auto-fill, and real-time stock availability verification.
  - **Pessimistic locking (`lockForUpdate`)** to prevent overselling during concurrent transactions.
  - Line items record historical cost basis (`unit_cost`) to ensure transparent and accurate Gross Profit calculations (Gross Sales - COGS).
  - Multi-payment support: Cash, Card (POS Terminal), Mobile Banking (bKash / Nagad / Rocket), and Bank Transfer.
  - Clean printable customer invoices with business identity branding.
- **Stock Adjustments & Traceable Audit Trail:**
  - Inventory reconciliation for Damaged Goods, Lost/Stolen Stock, Opening Stock, Physical Inventory Recounts, and Supplier Returns.
  - Strict validation preventing negative stock balances.
  - Unified **Stock Movements** ledger recording every single physical stock increment or decrement with timestamps, reference IDs, and user attribution.
- **Comprehensive Business Reports & Analytics:**
  - **Sales Report:** Daily, weekly, monthly, and custom date range filters, revenue breakdown, COGS, gross profit margins, and one-click **CSV export**.
  - **Purchases Report:** Supplier procurement breakdown, unit volume, and CSV export.
  - **Inventory Valuation Report:** Documented purchase cost basis (`Current Stock × Purchase Cost`), retail valuation (`Current Stock × Selling Price`), and potential profit margins.
  - **Stock Movement Audit Trail:** Chronological ledger of every stock transaction with CSV export.
  - **Best-Selling Products:** Top 10 items ranked by sales volume, revenue, and gross margin contribution.
- **Store Settings & Regional Customization:**
  - Customizable business name, address, contact phone, official email, currency symbol (default: **BDT / ৳**), and logo upload.

---

## 3. Technology Stack

- **Backend:** Laravel 11.x, PHP 8.2+
- **Database:** MySQL / MariaDB (Compatible with XAMPP 10.4+)
- **Frontend:** Blade Templates, Bootstrap 5.3.3, Bootstrap Icons 1.11.3
- **Visualization:** Chart.js 4.4.2
- **Testing:** PHPUnit 10.5 with SQLite / MySQL support

---

## 4. System Requirements

- **Operating System:** Windows 10/11, macOS, or Linux
- **Web Server / Stack:** XAMPP (Apache & MySQL/MariaDB) or standalone PHP & MySQL
- **PHP Version:** PHP 8.2 or higher (Required extensions: `pdo`, `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`)
- **Composer:** Composer 2.x
- **Web Browser:** Google Chrome, Microsoft Edge, Firefox, or Safari

---

## 5. Installation Instructions (Windows & XAMPP)

### Step 1: Start MySQL in XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to the **MySQL** module (Port 3306).
   *(Alternatively, if running standalone mysqld: `D:\Php_file\mysql_start.bat` or `C:\xampp\mysql_start.bat`)*

### Step 2: Clone or Open Project Directory
Navigate to the project root directory in PowerShell or Command Prompt:
```powershell
cd "c:\Users\user\Downloads\New folder\Inventory & Stock Management System"
```

### Step 3: Install Composer Dependencies
```powershell
composer install
```

### Step 4: Environment Configuration
Create the `.env` file from `.env.example`:
```powershell
copy .env.example .env
```

Ensure the following database variables are set in `.env`:
```env
APP_NAME="StockMaster Pro"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Dhaka
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
```

### Step 5: Generate Application Key
```powershell
php artisan key:generate
```

### Step 6: Create the MySQL Database
You can create the database via phpMyAdmin (`http://localhost/phpmyadmin`) or via CLI:
```powershell
php -r "(new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''))->exec('CREATE DATABASE IF NOT EXISTS inventory_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');"
```

### Step 7: Run Migrations and Seed Initial Data
Execute the migrations to generate all required tables and relationships, and populate initial seed data:
```powershell
php artisan migrate --seed
```

### Step 8: Link Storage Directory for Uploaded Images
```powershell
php artisan storage:link
```

---

## 6. Administrator Account Creation

You can create an administrator account at any time using the custom Artisan command:

```powershell
php artisan make:admin --name="Admin User" --email="admin@stockmaster.com" --password="password123"
```
Or run interactively:
```powershell
php artisan make:admin
```

---

## 7. Default Demo Accounts

If you ran `php artisan db:seed`, the following accounts are pre-configured:

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@stockmaster.com` | `password123` | Full access to all modules, reports, users, settings |
| **Sales Staff** | `staff@stockmaster.com` | `password123` | Access to POS / Sales, stock inquiry, view reports |

---

## 8. Running the Application

Start the Laravel development server:
```powershell
php artisan serve --port=8000
```

### Default Application URL
Open your web browser and visit:
```
http://127.0.0.1:8000
```
*(You will be greeted by the login screen where you can use the one-click demo filler buttons or input your credentials.)*

---

## 9. Automated Testing Instructions

The application includes an automated test suite verifying Authentication, RBAC, Catalog & Category safety, Purchases, Sales with Pessimistic Locking, Stock Adjustments, and Report CSV generation:

To run all automated tests:
```powershell
php artisan test
```

All 21 test suites execute in ~1 second with SQLite in-memory isolation.

---

## 10. Security Notes

1. **Restricted Registration:** Public user self-registration is deliberately disabled. Only existing administrators can provision new staff or admin credentials via the **Staff & Roles** dashboard module (`/users`) or via CLI (`php artisan make:admin`).
2. **Account Deactivation:** Inactive users cannot authenticate; active sessions are invalidated immediately if an administrator deactivates the user account.
3. **Pessimistic Concurrency Control:** Sales transactions apply `lockForUpdate()` on product records to prevent race conditions and stock overselling during simultaneous checkouts.
4. **CSRF & Input Validation:** All forms are protected by CSRF tokens and validated using dedicated Form Request classes.

---

## 11. Costing & Valuation Methodology

- **Cost of Goods Sold (COGS):** When an item is sold, the product's historical purchase cost is stored directly on the `sale_items` record. Gross Profit is strictly calculated as `Gross Revenue - COGS - Discount`. Revenue is never presented as net profit.
- **Inventory Valuation:** Valued under the Standard Purchase Cost Basis:
  $$\text{Inventory Valuation} = \sum (\text{Current Stock Quantity} \times \text{Unit Purchase Cost})$$
  $$\text{Retail Potential Valuation} = \sum (\text{Current Stock Quantity} \times \text{Unit Selling Price})$$

---

## 12. Troubleshooting Common Issues

### 1. `SQLSTATE[HY000]: General error: 1273 Unknown collation: 'utf8mb4_0900_ai_ci'`
- **Cause:** MariaDB 10.4 in XAMPP does not support MySQL 8.0's `utf8mb4_0900_ai_ci` collation.
- **Fix:** In `.env`, ensure `DB_COLLATION=utf8mb4_unicode_ci` is set. This is already pre-configured in this repository.

### 2. `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`
- **Cause:** MySQL service is not running on port 3306.
- **Fix:** Open XAMPP Control Panel and start the MySQL module, or execute `D:\Php_file\mysql_start.bat`.

### 3. Missing Product Images in Browser
- **Cause:** Symbolic link to storage directory has not been created.
- **Fix:** Run `php artisan storage:link`.

---

## 13. License

This project is open-source software licensed under the [MIT License](LICENSE).
