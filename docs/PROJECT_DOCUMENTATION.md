# CivicTrack Project Documentation

## 1. Introduction
CivicTrack is a community issue reporting and management platform that connects citizens with administrators and field staff.

## 2. Problem Statement
Community problems are often difficult to report, monitor, assign, and verify. CivicTrack provides a transparent workflow for submission, administrative review, staff assignment, resolution, and citizen feedback.

## 3. Objectives
- Improve citizen access to local reporting
- Increase administrative accountability
- Protect assignment and report data through authorization
- Preserve status history and resolution evidence
- Provide actionable database-driven dashboards

## 4. Scope
The project includes public pages, citizen and staff accounts, administrator management, reports, assignments, progress updates, notifications, feedback, analytics, and seed demo data.

## 5. Functional Requirements
Public registration creates citizen accounts only. Administrators manage categories, departments, users, reports, assignments, and feedback. Staff members access only their assignments. Citizens can submit, track, cancel eligible reports, and rate resolved reports.

## 6. Non-functional Requirements
The application uses server-side validation, CSRF protection, password hashing, secure file validation, responsive Bootstrap UI, normalized MySQL tables, transactions, and pagination.

## 7. Roles and Use Cases
- Administrator: review, approve, reject, assign, monitor, and manage
- Citizen: register, report, track, cancel, and provide feedback
- Staff: investigate, update, add evidence, and resolve

## 8. Architecture
CivicTrack follows Laravel MVC architecture. Controllers handle HTTP workflows, Eloquent models define relationships and casts, Blade renders views, middleware enforces role access, policies enforce ownership, and transactions protect multi-step changes.

## 9. Database Design
Main entities are users, categories, departments, reports, report attachments, assignments, status histories, progress updates, progress attachments, feedback, and notifications. Foreign keys enforce relationships and unique constraints prevent duplicate report feedback and staff/report assignment pairs.

## 10. Entity Relationship Summary
- A user has many reports as citizen
- A category has many reports
- A department has many reports
- A report has many attachments, assignments, status histories, and progress updates
- A report has one feedback record
- An assignment belongs to a report and staff user
- A progress update has many attachments

## 11. Installation
1. Install XAMPP, PHP 8.3+, Composer, Node.js, and npm.
2. Start Apache and MySQL.
3. Create a MySQL database named `civictrack`.
4. Configure `.env`.
5. Run Composer and npm installation.
6. Run migrations and seeders.
7. Run the Laravel development server.

## 12. Configuration
Set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=civictrack`, `DB_USERNAME=root`, and `DB_PASSWORD` for XAMPP. Set `APP_URL` to the development server URL.

## 13. Demo Accounts
Use the credentials documented in `README.md`. All demo accounts are fictional and for local demonstration only.

## 14. Testing
Automated feature tests cover citizen registration, invalid login, role dashboard restrictions, report ownership, report creation, image validation, administrator approval and assignment, and feedback eligibility/duplication.

Run:

```powershell
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan route:list
php artisan optimize:clear
```

## 15. Future Improvements
- Email delivery for password resets and notifications
- GIS map integration
- Mobile application API
- SLA and escalation tracking
- Cloud object storage
- Audit log export and advanced reporting
