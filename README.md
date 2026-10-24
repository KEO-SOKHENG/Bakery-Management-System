# Bakery Management System

A modern, full-stack Bakery Management System built with **Laravel 13**, **PHP 8.3**, and **PostgreSQL**, containerized with **Docker** and deployed on **Render**.

The application streamlines front-of-house point-of-sale (POS) operations, back-of-house production batch tracking, raw ingredient inventory control, supplier procurement, customer loyalty programs, and comprehensive financial reporting.

---

## Table of Contents
- [Architecture & Tech Stack](#architecture--tech-stack)
- [System Modules & Capabilities](#system-modules--capabilities)
- [User Roles & Access Control](#user-roles--access-control)
- [Local Development Setup](#local-development-setup)
- [Testing & Quality Assurance](#testing--quality-assurance)
- [Production Deployment (Render & Docker)](#production-deployment-render--docker)
- [Environment Variables & Security](#environment-variables--security)
- [Operational Safety & Database Integrity](#operational-safety--database-integrity)

---

## Architecture & Tech Stack

* **Backend Framework:** Laravel 13.x (PHP 8.3)
* **Database:** PostgreSQL (Render Managed PostgreSQL in production; SQLite in-memory for automated testing)
* **Web Server & Containerization:** Docker (multi-stage build), Apache 2.4, custom dynamic port binding for Render (`entrypoint.sh`)
* **Frontend Tooling:** Vite 8.x, Laravel Vite Plugin, TailwindCSS utility integration, Vanilla CSS design tokens with responsive layout components
* **Typography & Icons:** Plus Jakarta Sans, SVG vector iconography
* **Document Generation:** `barryvdh/laravel-dompdf` for styled PDF invoice and report exports; native streamed CSV/Excel reports
* **Testing:** PHPUnit 12.x (196 automated unit/feature tests), Playwright E2E testing suite

---

## System Modules & Capabilities

### 1. Point of Sale (POS) Terminal
* Touch-friendly, high-velocity cashier checkout interface.
* Product grid with category filters, instant keyword search, and out-of-stock disablement.
* Interactive cart with quantity adjustments, custom order notes, and user-specified line discounts.
* Walk-in and registered customer selection with instant loyalty point calculation.
* Thermal slip modal preview and browser printing (`window.print()`).
* Real-time stock reservation with database row locking (`lockForUpdate`) to prevent overselling.

### 2. Order Management & Invoices
* Full order lifecycle tracking: `Pending` → `Preparing / Baking` → `Ready for Pickup` → `Out for Delivery` → `Completed` / `Cancelled`.
* Multi-item order creation with automatic subtotal, discount, tax, and grand total calculations.
* Automatic `Sale` and `Payment` ledger creation upon payment completion.
* Printable invoices with configurable store headers, tax registration numbers, and receipt footers.
* Safe administrative order maintenance tool with dry-run purge preview and optional stock restoration.

### 3. Product Catalog & Categories
* Products catalog with category assignment, automated SKU generation (`PRD-0001`), pricing, and unit cost.
* Shelf-life tracking with automated expiry date computation.
* Image upload with validation and automated file cleanup on deletion.
* Referential integrity protection: prevents deleting products referenced in sales, production batches, or recipes.
* Category management with unique name validation and product-count safety checks.

### 4. Ingredients & Inventory Control
* Raw ingredient stock monitoring with unit tracking (kg, g, L, pcs) and unit cost.
* Minimum quantity alert thresholds (`Low Stock` and `Out of Stock` visual badges).
* Automated `StockMovement` audit ledger entries created upon ingredient creation and manual adjustments.
* Referential integrity protection: prevents deleting ingredients referenced in recipes or purchase orders.

### 5. Recipes & Formulations
* Baking formulation formulas linked to catalog products.
* Multi-ingredient recipe composition with exact quantities and units.
* Automated recipe batch cost calculation and production time estimation.

### 6. Procurement & Purchase Orders
* Supplier and vendor directory with contact details and payment terms.
* Purchase order workflow: `Draft` → `Ordered` → `Received` → `Cancelled`.
* Receiving workflow: automatically increments ingredient stock and creates stock-in movement ledgers.

### 7. Bakery Production Tracking
* Daily baking batch planning with assigned head and pastry bakers.
* Batch status tracking: `Scheduled` → `In Progress` → `Completed`.
* Automated consumption: deducts recipe ingredients from stock when batches finish.

### 8. Customer Management & Loyalty
* Customer profiles with purchase history, contact information, and order metrics.
* Automated loyalty point accumulation and tiered rewards (`Standard`, `Silver`, `Gold`, `Platinum`).
* Quick-customer registration modal directly within the POS checkout terminal.

### 9. Financial & Operational Reports
* 11 comprehensive reporting modes: Overview, Sales, Revenue, Orders, Products, Inventory, Low Stock, Production, Customers, Purchases, and Profit & Loss.
* Flexible date filtering: Today, Yesterday, 7 Days, 30 Days, This Month, Last Month, or Custom Date Ranges.
* Styled PDF report downloads and streamed Excel/CSV exports.

### 10. Staff HR Extensions
* Daily employee attendance check-in/check-out records.
* Work schedule and shift assignment management.
* Salary records and payment disbursement tracking.

### 11. System Settings & Backup Management
* Store configuration: store name, contact phone, contact email, address, tax rate, and currency symbol.
* Customizable thermal receipt headers and footers.
* Authenticated administrative JSON configuration backup creation, download, and restoration.

---

## User Roles & Access Control

Role authorization is strictly enforced by middleware (`CheckRole` and `EnsurePasswordNotExpired`):

| Role | Default Capabilities | Workflow Entry Point |
| :--- | :--- | :--- |
| **Admin (`admin`)** | Full, unrestricted access to all modules, staff user management, role permissions, system settings, backup/restore, and data purge tools. | `/admin/dashboard` |
| **Manager (`manager`)** | Complete operational management: POS, products, categories, inventory, recipes, suppliers, purchase orders, production batches, orders, reports, and HR schedules. Cannot delete admins or restore system backups. | `/manager/dashboard` |
| **Cashier (`cashier`)** | Front-of-house operations: POS terminal, checkout processing, order status viewing, and customer creation/lookup. | `/pos` (via `/cashier/dashboard`) |
| **Baker (`baker`)** | Kitchen production operations: View and update baking batch statuses, inspect recipe formulations, view ingredient stock levels. | `/admin/production` |

---

## Local Development Setup

### Prerequisites
* **PHP:** 8.3 or higher with extensions: `pdo`, `pdo_pgsql` (or `pdo_mysql`/`pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
* **Composer:** 2.x
* **Node.js:** 20.x or higher & npm
* **Database:** PostgreSQL 15+ (or MySQL / SQLite for local development)

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/KEO-SOKHENG/Bakery-Management-System.git
   cd Bakery-Management-System
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install frontend dependencies:**
   ```bash
   npm install
   ```

4. **Set up environment configuration:**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

5. **Configure your database in `.env`:**
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=bakery_db
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   ```

6. **Run migrations and seed development data:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   *Note: In local development, `DatabaseSeeder` provisions sample products, categories, suppliers, recipes, and default test accounts (password: `pass123`). In production (`APP_ENV=production`), default test accounts are automatically skipped.*

7. **Compile frontend assets:**
   ```bash
   # Production build
   npm run build

   # Or development mode with hot reloading
   npm run dev
   ```

8. **Start the local development server:**
   ```bash
   php artisan serve
   ```
   Visit `http://127.0.0.1:8000` to log in.

---

## Testing & Quality Assurance

The project includes automated test coverage across authentication, authorization, order workflows, calculations, and security safeguards:

```bash
# Run the complete test suite
php artisan test

# Run a specific test class
php artisan test --filter=ResetAdminPasswordTest
php artisan test --filter=OrderManagementTest
php artisan test --filter=ProductDeletionSafetyTest
```

---

## Production Deployment (Render & Docker)

The application is deployed on **Render** as a Web Service running Docker connected to a managed **Render PostgreSQL** database.

### Container Architecture
* [`Dockerfile`](./Dockerfile): Multi-stage container based on `php:8.3-apache`, installing PostgreSQL client libraries, PHP extensions (`pdo_pgsql`, `opcache`, `gd`), and copying pre-built Vite assets.
* [`docker/entrypoint.sh`](./docker/entrypoint.sh): Startup script that:
  1. Configures Apache to listen dynamically on Render's `${PORT}`.
  2. Ensures required storage directories exist with `775` permissions.
  3. Executes a read-only database diagnostic check (`php artisan db:diagnostic`).
  4. Runs migrations only if explicitly enabled (`RUN_MIGRATIONS=true`).
  5. Runs one-time administrator bootstrap if explicitly enabled (`BOOTSTRAP_ADMIN=true`).
  6. Runs one-time administrator password reset if explicitly enabled (`RESET_ADMIN_PASSWORD=true`).
  7. Starts Apache in the foreground (`apache2-foreground`).

---

## Environment Variables & Security

### Production Flags Reference

| Variable | Description | Safe Recommended Setting |
| :--- | :--- | :--- |
| `APP_ENV` | Application environment. | `production` |
| `APP_DEBUG` | Detailed error display. | `false` (never enable in production) |
| `RUN_MIGRATIONS` | Automatically executes `php artisan migrate --force` on startup. | Set to `true` only during a migration release, then remove or set to `false`. |
| `BOOTSTRAP_ADMIN` | Provisions initial administrator on a fresh database. | Set to `true` only during initial deployment. Refuses to overwrite existing accounts. |
| `BOOTSTRAP_ADMIN_NAME` | Full name for the bootstrap admin. | e.g. `System Administrator` |
| `BOOTSTRAP_ADMIN_USERNAME` | Username for the bootstrap admin. | e.g. `admin` |
| `BOOTSTRAP_ADMIN_EMAIL` | Email for the bootstrap admin. | e.g. `admin@bakery.com` |
| `BOOTSTRAP_ADMIN_PASSWORD` | Password for the bootstrap admin (min 8 chars). | Secure random password |
| `RESET_ADMIN_PASSWORD` | **One-time** emergency password reset for an existing admin. | **Must be deleted or set to `false` immediately after recovery.** |
| `RESET_ADMIN_USERNAME` | Exact username of the existing admin to reset. | Matches existing account |
| `RESET_ADMIN_EMAIL` | Exact email of the existing admin to reset. | Matches existing account |
| `RESET_ADMIN_NEW_PASSWORD` | New permanent password for the admin account. | Secure password |
| `RESET_ADMIN_MUST_CHANGE_PASSWORD` | Forces or bypasses the temporary password screen. | Set to `false` to grant direct dashboard access. |

---

## Operational Safety & Database Integrity

* **Never Reset Production Data:** The live Render PostgreSQL database contains actual business records, user accounts, and operational data. Never run `migrate:fresh`, `migrate:reset`, or `db:seed` against production.
* **One-Time Password Reset Flag:** The `RESET_ADMIN_PASSWORD=true` flag is an operational tool. Once the admin account is recovered, the flag must be unset in Render to prevent re-executing password resets on routine container restarts.
* **Zero Credential Logging:** Passwords and secrets are sanitized before console output or error logs are emitted. Plaintext credentials must never be committed to source control.

---

## License

This project is open-source software licensed under the [MIT License](LICENSE).
