# CivicTrack Project Documentation

## 1. Introduction
CivicTrack is a community issue reporting and management platform for Afghan municipalities. It connects citizens with administrators and field staff so that public problems — damaged roads, waste management, broken streetlights, water leaks, drainage issues — can be reported, reviewed, assigned, resolved, and verified with a transparent audit trail.

## 2. Problem Statement
Community problems are often difficult to report, monitor, assign, and verify. Citizens lack a reliable channel, administrators lack oversight, and resolution work is rarely documented. CivicTrack provides a structured workflow: submission, administrative review, staff assignment, investigation with evidence, resolution, and citizen feedback.

## 3. Objectives
- Improve citizen access to local reporting
- Increase administrative accountability through status history and audit trails
- Protect assignment and report data through role-based authorization
- Preserve resolution evidence (photos attached to progress updates)
- Provide actionable, database-driven dashboards per role

## 4. Scope
In scope: public pages, citizen registration/login, report submission with photo attachments, report tracking and cancellation, citizen feedback, administrator review (approve/reject), staff assignment, report reopening, user activation management, category and department CRUD, staff workspace with progress updates and evidence, internal notes, database notifications, and seed demo data in an Afghan context.

Out of scope: email delivery for notifications, GIS map integration, mobile API, SLA/escalation tracking, cloud object storage (see Section 19).

## 5. Functional Requirements
- **Citizens** register (public registration creates `citizen` role only), log in, submit reports with title, description, category, location (with optional GPS coordinates), priority, and 1–5 photos, track their own reports, cancel reports in `pending_review` or `approved` status, and submit feedback (rating 1–5 plus optional comment) on resolved reports.
- **Staff** see only their own assignments, view report details including citizen photos and status history, post progress updates (public or internal), attach evidence photos, and submit resolution with mandatory completion photos. The first progress update moves an `assigned` report to `in_progress`; resolution moves it to `resolved`.
- **Administrators** review new reports (approve/reject with a required rejection reason), assign approved reports to active staff (optionally setting the responsible department), reopen resolved reports, toggle user active/inactive state, create staff accounts, manage categories and departments (create/update/delete with integrity guards), and monitor all feedback.
- **Notifications**: database notifications are sent on report submission, approval, rejection, assignment, public progress updates, resolution, and reopening.

## 6. Non-functional Requirements
- Server-side validation on every mutating request (`$request->validate`)
- CSRF protection on all POST/PUT/DELETE forms (Laravel default)
- Password hashing via bcrypt (`hashed` cast); `Auth::attempt` with session regeneration
- Inactive accounts (`is_active = false`) are blocked at login and at the role middleware
- File upload validation: mime types `jpg/jpeg/png/webp`, max 2 MB per photo, max 5 photos
- File storage: uploads go to the private `local` disk and are streamed through the authorized `storage.file` route (never directly from a public directory)
- Normalized MySQL schema with foreign keys, unique constraints, and audit-preserving delete rules
- Responsive Bootstrap 5 UI, pagination on all listings, transactions around multi-step changes

## 7. Roles and Use Cases
| Role | Key Use Cases |
|------|---------------|
| Administrator | Review/approve/reject reports, assign staff, reopen reports, manage users/categories/departments, view feedback, monitor analytics |
| Citizen | Register, submit report, track status, cancel eligible report, rate resolved report |
| Staff | View own assignments, investigate, post progress updates (public/internal), attach evidence, resolve |

Public (unauthenticated) visitors can view Home, About, How It Works, and Contact pages, and access login/register/password reset.

## 8. Architecture
CivicTrack follows the Laravel MVC architecture (Laravel 13, PHP 8.3+, MySQL 8.0+):
- **Controllers**: HTTP workflow orchestration (`app/Http/Controllers/{Admin,Citizen,Staff,Shared,Auth}`)
- **Models**: Eloquent ORM with relationships and casts (`app/Models`)
- **Views**: Blade templates extending `layouts/public.blade.php` (public + auth) or `layouts/dashboard.blade.php` (all authenticated role dashboards)
- **Middleware**: `role:<roles>` route guard; `FileAccessAuthorization` appended to the `web` group
- **Policies**: `ReportPolicy` (view/update/cancel) and `AssignmentPolicy` (view/update) enforce ownership
- **Notifications**: `SystemNotification` via the `database` channel
- **Transactions**: `DB::transaction` protects report creation, status changes, progress updates, and resolution

Route map (54 total routes, 50 named):

| Group | Prefix/Name | Routes |
|-------|-------------|--------|
| Public | — | `home`, `about`, `how-it-works`, `contact` |
| File serving | `storage.file` | `GET /storage/{path}` — streams private-disk attachments through `FileAccessAuthorization` |
| Auth (guest) | — | `login`, `register`, `password.request`, `password.email`, `password.reset`, `password.update` |
| Shared (auth) | — | `dashboard` (role redirect), `logout`, `profile.edit`, `profile.update`, `notifications.index`, `notifications.read`, `notifications.read-all` |
| Citizen | `citizen.` | `dashboard`, `reports.index`, `reports.create`, `reports.store`, `reports.show`, `reports.cancel`, `reports.feedback` |
| Admin | `admin.` | `dashboard`, `users`, `users.create-staff`, `users.store-staff`, `users.toggle`, `categories` (+store/update/destroy), `departments` (+store/update/destroy), `reports.index`, `reports.show`, `reports.approve`, `reports.reject`, `reports.assign`, `reports.reopen`, `feedback` |
| Staff | `staff.` | `dashboard`, `assignments.index`, `assignments.show`, `assignments.progress`, `assignments.resolve` |

## 9. Database Design
Main entities (10 Eloquent models, 14 application tables):

| Table | Purpose | Key Columns & Constraints |
|-------|---------|---------------------------|
| `users` | All accounts | `email` unique; `role` enum(admin/citizen/staff) default citizen; `is_active` bool; `phone`; `profile_photo` |
| `categories` | Report categories | `name` unique, `slug` unique, `is_active` |
| `departments` | Responsible departments | `name` unique, `contact_email` nullable |
| `reports` | Core issue records | `reference_number` unique (format `CT-YYYYMMDD-XXXXXXXX`); `citizen_id` FK→users restrictOnDelete; `category_id` FK→categories restrictOnDelete; `department_id` FK→departments nullable nullOnDelete; `priority` enum(low/medium/high/urgent); `status` enum(7 statuses); `latitude`/`longitude` decimal(10,7); index on `(status, priority)` |
| `report_attachments` | Citizen photos | `report_id` FK cascade; `uploaded_by` FK→users restrictOnDelete; `file_path`, `original_name`, `file_type` |
| `assignments` | Staff assignments | `report_id` FK cascade; `staff_id` FK→users restrictOnDelete; `assigned_by` FK→users restrictOnDelete; `status` enum(pending/active/completed/reassigned); unique `(report_id, staff_id)` |
| `report_status_histories` | Audit trail | `report_id` FK cascade; `updated_by` FK→users restrictOnDelete; `previous_status`, `new_status`, `notes` |
| `progress_updates` | Staff updates | `report_id` FK cascade; `staff_id` FK→users restrictOnDelete; `description`; `is_internal` bool |
| `progress_attachments` | Evidence photos | `progress_update_id` FK cascade; `file_path`, `original_name` |
| `feedback` | Citizen ratings | `report_id` FK cascade; `citizen_id` FK→users restrictOnDelete; `rating` tinyint with MySQL CHECK `rating BETWEEN 1 AND 5`; unique `(report_id, citizen_id)` |
| `notifications` | Database notifications | uuid `id` PK; morphs `notifiable`; `data` JSON; `read_at` indexed (plus composite `(notifiable_type, notifiable_id, read_at)`) |
| `password_reset_tokens`, `sessions`, `cache`, `jobs` | Laravel infrastructure | — |

All foreign keys use `restrictOnDelete` on user/staff/category references to preserve audit history; `reports.department_id` uses `nullOnDelete` so removing a department never removes reports.

## 10. Entity Relationship Summary
- A **user** has many reports (as citizen, `citizen_id`) and many assignments (as staff, `staff_id`)
- A **category** has many reports; a **department** has many reports
- A **report** has many attachments, assignments, status histories, and progress updates
- A **report** has one feedback record; a **progress update** has many attachments
- An **assignment** belongs to a report and a staff user (unique per report/staff pair)

## 11. Report Lifecycle
```
pending_review ──approve──▶ approved ──assign──▶ assigned ──(first update)──▶ in_progress ──resolve──▶ resolved ──feedback──▶ (complete)
      │                        │                                           │
      ├──reject──▶ rejected    └──cancel (citizen, pending_review/approved)    └──reopen (admin) ──▶ approved
      └──cancel (citizen, pending_review/approved) ──▶ cancelled
```
Every transition writes a `report_status_histories` row (previous status, new status, actor, notes) and, where applicable, sends database notifications to the affected citizen, staff, and/or administrators.

## 12. Security Implementation
- **Role middleware** (`role:admin|staff|citizen`) aborts 403 for wrong role or inactive accounts
- **Policies**: `ReportPolicy::view/update/cancel` and `AssignmentPolicy::view/update` enforce ownership beyond the route guard
- **File access authorization** (`FileAccessAuthorization`, appended to the `web` group): all uploads are stored on the **private** `local` disk (`storage/app/private`, outside the web root — the `public/storage` symlink is not used), so no file is ever served directly by the web server. Every request to `storage/report-attachments/*`, `storage/progress-attachments/*`, and `storage/profile-photos/*` passes the middleware before the `storage.file` route streams it: unauthenticated requests are blocked; admins access all; citizens access only their own reports' attachments; staff access only assigned reports' attachments (and public progress evidence); internal progress evidence is never exposed to citizens
- Additional guards: staff cannot self-assign (assignment only via admin), citizens can only rate their own resolved reports (duplicates blocked by controller check and unique constraint), admins cannot deactivate their own account, and the storage route rejects path traversal and unknown files

## 13. Notification System
`App\Notifications\SystemNotification` delivers a message and target URL through the `database` channel. Events: new report submitted (to admins), report approved/rejected (to citizen), report assigned (to staff + citizen), public progress update (to citizen), report resolved (to citizen + admins), report reopened (to citizen + admins). Unread counts appear in the dashboard navbar; the notifications page supports per-item read and mark-all-read.

## 14. Installation
Requirements: PHP 8.3+, Composer 2.x, Node.js 18+/npm 9+, MySQL 8.0+ (XAMPP on Windows), Laravel 13.

```powershell
cd C:\xampp\htdocs\CivicTrack
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Create the database once (phpMyAdmin or CLI):

```sql
CREATE DATABASE civictrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Configure `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civictrack
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://127.0.0.1:8000
```

Apply schema changes without resetting existing tables. Never run `migrate:fresh`, `migrate:refresh`, or `db:wipe` against a database containing data you need to preserve:

```powershell
php artisan migrate          # applies pending migrations without dropping existing tables
npm run build                # compile frontend assets

# Optional demo data: inspect database/seeders/DatabaseSeeder.php first.
# It preserves farid@civictrack.com but deletes specific legacy @civictrack.local
# demo users and their related report records. Back up the database before running:
php artisan db:seed --class=DatabaseSeeder
php artisan serve --host=127.0.0.1 --port=8000
```

The seeder preserves the primary administrator (`farid@civictrack.com`) and creates Afghan-context sample data. However, it also deletes specifically listed legacy `@civictrack.local` demo users and related reports, assignments, progress updates, attachments, feedback, histories, and departments. Do not run it on an existing database until you have reviewed those cleanup queries and backed up any data you need. `firstOrCreate` prevents many duplicate demo records but does not make the cleanup operation non-destructive.

## 15. Configuration
Key `.env` settings are shown in Section 14. `APP_URL` must match the development server URL for notification links and password reset emails. Cache configuration may remain at the Laravel defaults (`file`/`database`).

## 16. Afghan Context
The seeder produces a complete Afghan-context dataset:
- **1 primary administrator**: Farid Ahmad Khan (`farid@civictrack.com`)
- **8 staff members** and **10 citizens** with Afghan names on the `@civictrack.com` domain
- **8 categories** (Roads and Transportation, Waste Management, Electricity and Street Lighting, Water Supply, Drainage and Sanitation, Public Infrastructure, Environmental Problems, Other Issues)
- **6 departments** (Kabul Municipal Services, Public Works, Water Supply, Sanitation and Waste Management, Electricity and Street Lighting, Environmental Protection)
- **20 reports** distributed across all 7 statuses, with reference numbers, Kabul-area coordinates, realistic Afghan locations (Shahr-e-Naw, Karte Seh, Dasht-e-Barchi, Herat, Kandahar, Mazar-i-Sharif, Jalalabad, ...), status histories, assignments, progress updates, and feedback for resolved reports

## 17. Demo Accounts
All demo credentials are documented in `README.md` (primary admin `farid@civictrack.com` / `Admin@123`; staff `Staff@123`; citizen `Citizen@123`). Accounts are fictional and for local development only.

## 18. Testing
The PHPUnit feature suite (`tests/Feature/CivicTrackTest.php`, 10 tests) plus `ExampleTest.php` — **11 tests, 26 assertions** — covers:
- Web route registration
- Citizen registration and role assignment
- Invalid login rejection
- Role-based dashboard access restrictions (citizen/staff cannot access admin)
- Cross-citizen report ownership enforcement
- Report creation with valid photo and status-history row
- Invalid (non-image) upload rejection
- Admin approve + assign workflow
- Feedback eligibility (resolved only) and duplicate prevention

Run:

```powershell
php artisan test            # test suite
npm run build               # frontend build
php artisan route:list      # verify routes
php artisan optimize:clear  # clear caches
```

## 19. Future Improvements
- Email delivery for password resets and notification digests
- GIS map integration for report locations
- Mobile application API
- SLA and escalation tracking
- Cloud object storage for attachments
- Audit log export and advanced reporting