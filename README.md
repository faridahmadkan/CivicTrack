# CivicTrack

CivicTrack is a Laravel 13 community issue reporting and management system for citizens, administrators, and assigned staff members.

## Main Features

- Citizen registration, login, password reset, and profile management
- Issue reporting with categories, priorities, locations, and image evidence
- Administrator review, approval, rejection, assignment, and user management
- Staff assignments, progress updates, internal notes, and completion evidence
- Status history, database notifications, citizen feedback, and ratings
- Database-driven dashboards with Chart.js analytics
- Role-based authorization and report ownership policies
- XAMPP/MySQL and Laravel Blade/Bootstrap 5 interface

## Technology

- PHP 8.5
- Laravel 13
- MySQL
- Blade and Bootstrap 5
- Vite
- Chart.js
- PHPUnit

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- XAMPP with Apache and MySQL

## Installation

```powershell
cd C:\xampp\htdocs\CivicTrack
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Create the `civictrack` database in phpMyAdmin, configure `.env`, and run:

```powershell
php artisan migrate:fresh --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`.

## Demo Accounts

| Role | Email | Password |
|---|---|---|
| Administrator | admin@civictrack.local | Admin@123 |
| Staff | aisha.staff@civictrack.local | Staff@123 |
| Citizen | samir@civictrack.local | Citizen@123 |

Demo credentials are for local development only.

## Verification

```powershell
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan route:list
php artisan optimize:clear
```

## Project Structure

- `app/Models` - Eloquent domain models
- `app/Http/Controllers` - Role and shared controllers
- `app/Http/Middleware` - Role middleware
- `app/Policies` - Resource authorization
- `database/migrations` - Database schema
- `database/seeders` - Demo data
- `resources/views` - Public, authentication, and dashboard Blade views
- `routes/web.php` - Named application routes
- `tests/Feature` - Application authorization and workflow tests

See [Project Documentation](docs/PROJECT_DOCUMENTATION.md) for architecture, database design, installation, testing, and future improvements.
