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
5. Open a job and choose **Save job** or **Apply on source site**. The source site
   handles the actual application. KoneKT never automatically sends your resume.
6. After submitting externally, choose **I've applied**. Manage self-reported
   progress and private notes at `/applications`. Opening a link alone is recorded
   as **Link opened**, not as a submission. Archived resume copies are owner-only.
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
- [JSearch Philippine integration and migration](docs/jsearch-integration.md)

## Real Philippine jobs

Set `JSEARCH_API_KEY` in your local `.env`, run `php artisan migrate`, and rebuild
the frontend. JSearch is enabled automatically when a key is configured. The key
stays on the server. `/jobs` searches Philippine listings with cursor pagination;
the homepage previews already-retrieved listings without another provider call.

Searches are cached for six hours. `JSEARCH_MONTHLY_LIMIT` defaults to 180 uncached,
single-page requests per calendar month; the database counter survives cache clears.
This is an application limit, not an account-wide guarantee for requests made by
other tools. API failures show a safe message and allow browsing retrieved listings.

Imported jobs do not need employer accounts or manually created company records.
Manual company, job-posting, and job-skill writes are retired. Admin/Manager/User
remain internal access permissions. Legacy ownership and business-role columns are
retained for historical data and do not grant employer access to job seekers' records.

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
