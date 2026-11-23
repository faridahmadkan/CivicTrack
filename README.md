# CivicTrack

CivicTrack is a Laravel 13 service-reporting and field-operations application. It supports public and registered-citizen reporting, department-scoped staff work, field-worker task workflows, and administrator configuration.

## Implemented workflows

- Guests submit reports and receive a one-time access code for protected tracking. Registered citizens can submit and follow their own reports.
- Reporters can cancel eligible reports before field work begins and dispute a closed outcome within the review window. Staff can uphold or reopen a disputed outcome; reopening starts a new SLA cycle.
- Staff review and route reports within their organization-unit scope. Administrators can configure units, categories, registration controls, and report SLA policies.
- Staff and field workers use separate workspaces and permissions. Applicants receive no operational role until approved; administrators can also provision active staff and worker accounts directly.
- A work order can contain multiple tasks and dependencies. A task can have a team of workers, retains assignment history, and waits for every active team member to submit before staff can verify the task.
- Report attachments, workforce documents, and task evidence are stored privately and served through authenticated, record-scoped download routes. Reporter-facing report pages only expose reporter-visible attachments.
- Reports retain status history, public or internal comments, routing decisions, elapsed-minute SLA records, escalations, reopening requests, citizen feedback, audit events, and account notifications.
- Staff receive advisory duplicate suggestions and can link reports, mark a report as a duplicate, or merge it into a canonical report through audited, department-scoped actions. Both reports and their original histories remain stored.
- The public community map shows only staff-published reports with generalized coordinates.

The application does not yet implement work-calendar SLA calculations, holidays or paused SLA timers, a read-tracked message center, worker skills/certifications, configurable service-area boundaries, dynamic category fields, inventory, or statistical worker performance scoring. A report merge preserves separate report rows and histories; it does not combine comments or attachments. See [docs/architecture.md](docs/architecture.md) for the audit record, data model, security boundaries, and release limitations.

## Requirements

- PHP 8.5 with the extensions required by Laravel and the installed Composer packages.
- A supported relational database. Automated tests use SQLite in memory.
- Node.js and npm for the Vite frontend build.
- A configured mail provider, queue worker, and Laravel scheduler for production notifications and SLA escalation.

## Local setup

For a new checkout:

```bash
composer install
npm ci
npm run build
```

Create `.env` from `.env.example` only if this checkout does not already have an `.env`; preserve any existing credentials. Run `php artisan key:generate` only for a new installation whose `APP_KEY` is empty. Configure the database, mail, queue, session, and cache settings there, then run the additive migrations:

```bash
php artisan migrate --no-interaction
php artisan db:seed --no-interaction
```

For an existing database, take and verify a backup before applying migrations. Never use `migrate:fresh`, `db:wipe`, or destructive seed logic with application data. The committed seeders use `firstOrCreate` and do not delete users or reports.

The required initial administrator identity is **Farid Ahmad Khan** (`farid@civictrack.com`). After migrations and base role seeding, set `CIVICTRACK_ADMIN_PASSWORD` to a strong, temporary secret in protected environment configuration and run:

```bash
php artisan civictrack:bootstrap-admin --no-interaction
```

The command creates that administrator only if the email does not already exist and refuses to change an existing account. Remove the bootstrap password from the environment after creation. Regular administrator provisioning can create citizen, staff, and field-worker accounts without opening public registration or applications; those accounts receive password setup and email verification instructions.

Fictional Afghan-context development units can be seeded by setting `CIVICTRACK_SEED_DEMO=true` in a local or test environment. A demo password of at least 16 characters must be provided as `CIVICTRACK_DEMO_PASSWORD` before labeled demo accounts are created. Demo users use `@civictrack.test` addresses. Demo seeding is not enabled in production and never updates existing account passwords. Do not put production credentials in demo settings.

To run the application locally, use the project's configured development scripts or run the Laravel server, queue worker, and scheduler as separate processes.

## Verification

```bash
php artisan test --compact
npm run build
```

The test configuration uses SQLite `:memory:` and does not migrate or seed the configured application database. The current automated suite does not replace deployment-database backup, restore, accessibility, or production mail/queue checks.
