# KoneKT

A Laravel 13, React 19, and Inertia 3 job portal with dedicated screens for job
discovery, resume matches, profiles, saved jobs, and applications.

## Local setup

Install PHP 8.3+, Composer 2, and Node 22.12+. PHP needs Laravel's standard
extensions and PDO SQLite. Enable PDO MySQL when using MySQL.

On this Windows workspace, `. .\scripts\activate.ps1` enables the existing
portable PHP and Composer installation. Other machines use their own runtime.

```powershell
composer install
npm.cmd ci --ignore-scripts
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
php artisan migrate
php artisan db:seed
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open <http://127.0.0.1:8000>. For frontend development, run `npm.cmd run dev`
in a second terminal. On Linux/macOS use `npm` rather than `npm.cmd`.

When updating an existing checkout, run `composer install`, `php artisan migrate`,
and `npm.cmd run build`. Preserve your existing `.env` and application key.

## Project-local PHP on Windows

This checkout includes an ignored `.tools/php` runtime. In each PowerShell terminal,
activate it before Composer, Artisan, or a frontend build:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
. .\scripts\activate.ps1
php -v # Must report PHP 8.3 or newer.
```

If `.tools/php` is absent, install PHP 8.3+ and ensure its directory precedes
XAMPP PHP in your PATH. The Composer platform setting does not upgrade PHP.
Run `composer check-platform-reqs --lock` to check the actual runtime.

## Local test account

| Field    | Value              |
| -------- | ------------------ |
| Email    | `test@example.com` |
| Password | `password`         |

The seeder creates this account and three clearly labeled sample jobs only in
local/testing environments. These credentials are for development only.

## Walkthrough

1. Browse `/jobs` without signing in. Search by role, company, or location.
   Arrangement, type, sort, and pagination are applied on the server and retained
   in the URL. Open a role to see its complete description and requirements.
2. Sign in or register. Returning users land on `/dashboard`, a compact overview
   with links to profile tasks, applications, saved jobs, and matches.
3. Open `/profile`. Use its sections to save personal details, add or edit career
   history, manage skills, and upload a resume.
4. Upload a text-based PDF of at most 5 MB. Image-only scanned PDFs cannot be
   matched. Uploading takes you to `/matches`, where each result explains its
   shared keywords. Refresh matches to include newly posted roles.
5. Open a job and choose **Save job** or **Send application**. Applications require
   a resume. A private copy is attached to the application, so replacing your
   profile resume does not change a submitted application.
6. Revisit your shortlist at `/saved-jobs` and track submissions at `/applications`.
   Only the applicant and the role's employer can download an attached resume.
7. Use **Forgot password?** on sign-in for account recovery. Configure mail for
   real email delivery; local `log` mail writes reset messages to Laravel's log.
8. Account deletion is in `/settings`, with an exact-email confirmation. It
   permanently removes related data and private application-resume copies.

Keyword overlap is a search aid, not an assessment of suitability or a hiring
decision. The current web workspace serves job seekers. Employer-management and
networking/chat screens are not advertised as completed features. Files uploaded
outside Laravel's active route/view and React page directories are retained as
reference, and are not automatically connected to the application.

## Backend and flow contracts

- [Backend resource contract](docs/activity-3-backend.md)
- [Job-seeker web flow](docs/job-seeker-flow.md)
- [Activity 5 authentication and access control](docs/activity-5-auth-access-control.md)

Activity 5 adds a staff skill catalog at `/staff/skills` for Admins and Managers,
and user role assignment at `/admin/users` for Admins. Run `php artisan db:seed`
to seed the default Spatie permissions and assign User to existing unassigned
accounts. Local demo staff accounts are `admin@example.com` and
`manager@example.com`, both with password `password`; they are never created in
production. New registrations receive User access only. Staff links appear only
when the authenticated account has the required role and permission.

Existing `/backend` JSON resources retain session authentication, CSRF protection,
validation, and ownership rules. The new web forms reuse those resource validators
where appropriate and respond with Inertia-compatible redirects.

## Verification

Build frontend assets before feature tests; the Blade entry loads page chunks
from Vite's manifest.

```powershell
npm.cmd run build
npm.cmd run types:check
npm.cmd run check
composer test
```

Feature tests use SQLite in memory. Run migrations and tests against the actual
MySQL server before a MySQL deployment. Do not import the legacy SQL schema over
a database already managed by migrations.
