# CivicTrack

CivicTrack is a Laravel 13 community issue reporting and management system designed for Afghan municipalities. It connects citizens with administrators and field staff to report, track, and resolve local community problems such as damaged roads, waste management, broken streetlights, water leaks, drainage issues, and other public infrastructure concerns.

## Main Features

- **Citizen Portal**: Registration, login, password reset, profile management, issue reporting with photos, location, and priority
- **Administrator Dashboard**: Report review (approve/reject), staff assignment, user management, category/department management, feedback monitoring, analytics with Chart.js
- **Staff Workspace**: Assigned reports, progress updates with evidence photos, internal notes, resolution submission
- **Complete Workflow**: Pending Review → Approved → Assigned → In Progress → Resolved (with feedback) / Rejected / Cancelled
- **Security**: Role-based access control (policies + middleware), file access authorization, CSRF protection, password hashing, SQL injection prevention
- **Notifications**: Real-time database notifications for all workflow events
- **Audit Trail**: Full status history with timestamps, user attribution, and notes

## Technology Stack

- **Backend**: PHP 8.3+, Laravel 13, MySQL 8.0+
- **Frontend**: Blade templates, Bootstrap 5, Bootstrap Icons, Chart.js
- **Build**: Vite, Tailwind CSS (utility classes)
- **Testing**: PHPUnit, Laravel testing utilities
- **Development**: XAMPP, Composer, Node.js/npm

## Requirements

- PHP 8.3 or newer
- Composer 2.x
- Node.js 18+ and npm 9+
- MySQL 8.0+ (or MariaDB 10.5+)
- XAMPP with Apache and MySQL (Windows) or equivalent

## Installation

### 1. Clone and Install Dependencies

```powershell
cd C:\xampp\htdocs\CivicTrack
composer install
npm install
```

### 2. Environment Configuration

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civictrack
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://127.0.0.1:8000
```

### 3. Database Setup

Create the database in phpMyAdmin or MySQL CLI:

```sql
CREATE DATABASE civictrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations (safe for existing data):

```powershell
php artisan migrate
```

### 4. Seed Demo Data (Optional)

The seeder creates Afghan-context demo data including the primary administrator, staff, citizens, departments, categories, and realistic community reports.

**For fresh installation:**
```powershell
php artisan db:seed --class=DatabaseSeeder
```

**To add demo data to existing database (idempotent, safe to run multiple times):**
```powershell
php artisan db:seed --class=DatabaseSeeder
```

### 5. Build Frontend Assets

```powershell
npm run build
```

### 6. Start Development Server

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000` in your browser.

## Demo Accounts

All demo accounts use the `@civictrack.com` domain and are **fictional** for local development only.

| Role | Name | Email | Password |
|------|------|-------|----------|
| **Primary Administrator** | Farid Ahmad Khan | farid@civictrack.com | Admin@123 |
| Staff | Ahmad Wali | ahmad.wali@civictrack.com | Staff@123 |
| Staff | Mohammad Zahir | mohammad.zahir@civictrack.com | Staff@123 |
| Staff | Hamidullah Rahimi | hamid.rahimi@civictrack.com | Staff@123 |
| Staff | Jawad Ahmadi | jawad.ahmadi@civictrack.com | Staff@123 |
| Staff | Bilal Sediq | bilal.sediq@civictrack.com | Staff@123 |
| Staff | Maryam Safi | maryam.safi@civictrack.com | Staff@123 |
| Staff | Fatima Noori | fatima.noori@civictrack.com | Staff@123 |
| Staff | Laila Mohammadi | laila.mohammadi@civictrack.com | Staff@123 |
| Citizen | Ahmad Wali | ahmad.wali.citizen@civictrack.com | Citizen@123 |
| Citizen | Mohammad Zahir | mohammad.zahir.citizen@civictrack.com | Citizen@123 |
| Citizen | Hamidullah Rahimi | hamid.rahimi.citizen@civictrack.com | Citizen@123 |
| Citizen | Jawad Ahmadi | jawad.ahmadi.citizen@civictrack.com | Citizen@123 |
| Citizen | Bilal Sediq | bilal.sediq.citizen@civictrack.com | Citizen@123 |
| Citizen | Maryam Safi | maryam.safi.citizen@civictrack.com | Citizen@123 |
| Citizen | Fatima Noori | fatima.noori.citizen@civictrack.com | Citizen@123 |
| Citizen | Laila Mohammadi | laila.mohammadi.citizen@civictrack.com | Citizen@123 |
| Citizen | Zahra Azimi | zahra.azimi@civictrack.com | Citizen@123 |
| Citizen | Nasir Mustafa | nasir.mustafa@civictrack.com | Citizen@123 |

> **Security Note**: These credentials are for local development only. Never use these passwords in production. The seeder is idempotent — running it multiple times will not create duplicates.

## Afghan Context

CivicTrack is designed for use in Afghanistan with:
- **Realistic Afghan names** for all demo users (e.g., Ahmad Wali, Maryam Safi, Hamidullah Rahimi)
- **Authentic locations** across major cities: Kabul (Shahr-e-Naw, Karte Seh, Dasht-e-Barchi, etc.), Herat, Kandahar, Mazar-i-Sharif, Jalalabad
- **Relevant departments**: Kabul Municipal Services, Public Works, Water Supply, Sanitation & Waste Management, Electricity & Street Lighting, Environmental Protection
- **Community issues**: Potholes, garbage, broken streetlights, water leaks, drainage, waste burning, damaged infrastructure

## Verification

```powershell
# Run migrations (safe for existing data)
php artisan migrate

# Seed demo data (idempotent)
php artisan db:seed --class=DatabaseSeeder

# Run test suite
php artisan test

# Build production assets
npm run build

# Verify routes
php artisan route:list

# Clear caches
php artisan optimize:clear
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # Admin dashboard, users, categories, departments, reports
│   │   ├── Auth/        # Login, register, password reset
│   │   ├── Citizen/     # Citizen dashboard, reports
│   │   ├── Staff/       # Staff dashboard, assignments
│   │   └── Shared/      # Notifications, profile
│   ├── Middleware/      # RoleMiddleware, FileAccessAuthorization
│   └── Policies/        # ReportPolicy, AssignmentPolicy
├── Models/              # 10 Eloquent models with relationships
├── Notifications/       # SystemNotification (database)
database/
├── migrations/          # 13 migrations + 3 index/constraint migrations
├── seeders/             # DatabaseSeeder (Afghan context)
├── factories/           # UserFactory
resources/
├── views/
│   ├── admin/           # Admin dashboard, management pages
│   ├── citizen/         # Citizen dashboard, report submission/tracking
│   ├── staff/           # Staff dashboard, assignments
│   ├── auth/            # Login, register, password reset
│   ├── public/          # Home, about, how-it-works, contact
│   ├── layouts/         # Public and dashboard layouts
│   └── shared/          # Notifications, profile
├── css/app.css          # Tailwind + custom styles
└── js/app.js            # Minimal JS entry point
routes/web.php           # 54 routes (50 named)
tests/Feature/           # 10 feature tests (26 assertions)
```

## Key Implementation Details

### Authorization
- **Middleware**: `role:admin|staff|citizen` on route groups
- **Policies**: `ReportPolicy` (view/update/cancel), `AssignmentPolicy` (view/update)
- **File Access**: uploads are stored on the private `local` disk (`storage/app/private`, outside the web root); `FileAccessAuthorization` middleware guards the `GET /storage/{path}` route so only authorized users (owner citizen, assigned staff, admin) can stream attachments; internal evidence is never exposed to citizens

### Report Workflow
```
pending_review → approved → assigned → in_progress → resolved
                    ↓
                 rejected
                    ↓
                 cancelled (citizen only, in pending_review/approved)
```

### Database Optimizations
- Indexes on `reports.citizen_id`, `reports.status`, `assignments.staff_id`, `notifications.read_at`
- Foreign keys with `restrictOnDelete` to preserve audit history
- MySQL CHECK constraint on `feedback.rating` (1-5)

## License

MIT License - See LICENSE file for details.

## Documentation

See [Project Documentation](docs/PROJECT_DOCUMENTATION.md) for:
- Functional & non-functional requirements
- Use cases and role descriptions
- Database design (ERD, entity relationships)
- System architecture
- Installation & configuration details
- Testing plan
- Future improvements