# Supplier App

An ERP-lite Supplier Management System built with Laravel 12. Covers procurement, sales, kitchen ordering (Purchase Request engine), receiving, margin reporting, KPI tracking, and more.

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+ / MariaDB 10.3+
- Node.js & npm (for compiling assets, if needed)
- XAMPP, Laragon, or any local PHP server stack

---

## Installation

### 1. Clone the repository

```bash
git clone <repo-url> supplier-app
cd supplier-app
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Copy the environment file

```bash
cp .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Configure the database

Open `.env` and update the database settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supplier_demo
DB_USERNAME=root
DB_PASSWORD=
```

> Create the database in MySQL first:
> ```sql
> CREATE DATABASE supplier_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> ```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Create the storage symlink

Required for uploaded files (supplier photos, proof images, certifications) to be publicly accessible.

```bash
php artisan storage:link
```

### 8. (Optional) Seed demo data

If a seeder is available:

```bash
php artisan db:seed
```

### 9. Start the development server

```bash
php artisan serve
```

The app will be available at [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## XAMPP Setup (Alternative)

If you prefer to run through XAMPP instead of `artisan serve`:

1. Place the project folder inside `C:\xampp\htdocs\` (e.g. `C:\xampp\htdocs\supplier-app`)
2. Make sure Apache and MySQL are running in the XAMPP Control Panel
3. Access the app via `http://localhost/supplier-app/public`

> Tip: For a cleaner URL, point a virtual host to the `public/` directory.

---

## Key Features

| Module | Description |
|---|---|
| Master Data | Regions, Suppliers, Products, Units, Kitchens, Certifications |
| Procurement | Purchase Orders, Complaints, Returns |
| Purchase Requests | Kitchen-based requisitions with auto ingredient generation from menu recipes |
| Receiving | Multi-kitchen receiving per PO with proof images and stock movement |
| Sales | Sales Orders with frozen selling price |
| RAB | Menu budgeting with COGS/margin per portion |
| Margin Reports | Daily & monthly margin reports with CSV export |
| Supplier KPI | Monthly KPI scoring with complaint penalties |
| Boss Dashboard | Analytics — revenue, top menus, best suppliers, complaint trends |

---

## Scheduled Commands

The app includes a scheduled KPI calculation command. To run it automatically, set up a cron job on your server:

```
* * * * * cd /path/to/supplier-app && php artisan schedule:run >> /dev/null 2>&1
```

On Windows (XAMPP), you can run it manually when needed:

```bash
php artisan kpi:monthly --month=YYYY-MM
```

---

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Database**: MySQL
- **Frontend**: Bootstrap 5 (Blade templates)
