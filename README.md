# KoneKT

KoneKT is a Laravel 13 job portal built with React 19 and Inertia 3. The current
web flow includes public job discovery, resume PDF upload and keyword matching,
sign-in/registration, and a signed-in profile dashboard.

## Local setup

Install PHP 8.3+, Composer 2, and Node 22.12+. PHP needs Laravel's standard
extensions plus PDO SQLite; enable PDO MySQL when using MySQL.

From PowerShell:

```powershell
composer install
npm.cmd ci --ignore-scripts
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
php artisan migrate
php artisan db:seed
npm.cmd run build
```

Start the backend and frontend development server in separate terminals:

```powershell
php artisan serve
```

```powershell
npm.cmd run dev
```

Open <http://127.0.0.1:8000>. The local seeder safely creates the test account,
skills, and sample job listings. It only creates the test account and demo jobs
when `APP_ENV` is `local` or `testing`; it is safe to run again.

The local portable `.tools` runtime is excluded from Git. On this existing
Windows workspace, `. .\scripts\activate.ps1` activates it when that local
script is present. Other machines use their own PHP and Composer installation.

## Test login

Use this account for local testing:

| Field | Value |
| --- | --- |
| Email | `test@example.com` |
| Password | `password` |

These credentials are for local/testing environments only. Do not use them in
production. You can also create a separate account at <http://127.0.0.1:8000/register>.

## Website walkthrough

1. Open the homepage at <http://127.0.0.1:8000>. You can browse jobs without
	signing in. The local seed adds three sample roles marked as preview data.
2. Search by title, company, location, or description. Filter by work
	arrangement and job type, sort by newest or title, and expand a role to read
	its requirements and responsibilities.
3. Select **Sign in** in the navbar and use the local test credentials above, or
	register a new account.
4. On the homepage, upload a text-based PDF resume (maximum 5 MB). KoneKT extracts
	its text and ranks open roles by weighted keyword overlap. After upload, the
	page jumps to **Matches from your resume**. Scanned image-only PDFs cannot be
	matched yet; the score is a keyword signal, not an AI or hiring decision.
5. Select **Dashboard** in the navbar to review profile completion, personal
	details, resume, skills, work experience, education, applications, saved
	jobs, and resume matches. Use **Download PDF** or **Replace** in the resume
	section as needed.
6. Use **Sign out** to end the session. To remove the local test account, open
	**Delete account** at the bottom of the dashboard and type
	`test@example.com` exactly. Account deletion is permanent and removes its
	related profile data and private resume.

## Backend integration

Read [the backend contract](docs/activity-3-backend.md) for resource paths,
payloads, response codes, dates, ownership, and frontend integration. The
`/backend` endpoints return JSON and require session authentication and CSRF
tokens. The current browser screens use `/`, `/login`, `/register`, and
`/dashboard`.

Job posting, applications, networking, and chat have backend resources, but
their complete employer/member web workflows are not part of the current UI
walkthrough yet.

## Verification

```powershell
php artisan test
npm.cmd run check
npm.cmd run types:check
npm.cmd run build
npm.cmd audit
```

Backend tests use SQLite in memory. Production builds are checked separately.
MySQL deployment should run migrations and tests against its actual server
before release. The original SQL schema and demo data are reference files; do
not import the old schema into a database already managed by migrations.
# KonektLV

Laravel 13 job-portal backend with React 19 and Inertia 3. Activity 3 includes
the 16-table domain schema, protected Eloquent models and relationships,
validated resource controllers, and modular routes.

## New checkout

Install PHP 8.3+, Composer 2, and Node 22.12+ with npm. PHP needs Laravel's
standard extensions plus PDO SQLite; enable PDO MySQL when using MySQL.

```powershell
composer install
npm.cmd ci --ignore-scripts
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
php artisan migrate
php artisan db:seed --class=SkillSeeder
npm.cmd run build
php artisan serve
```

Open http://localhost:8000. For live frontend development, run `npm.cmd run dev`
in another terminal. On Linux/macOS use `npm` rather than `npm.cmd`.

The local portable `.tools` runtime is excluded from Git. On this existing
Windows workspace, `. .\scripts\activate.ps1` activates it when that local
script is present. Other machines use their own PHP and Composer installation.

## Backend integration

Read [the backend contract](docs/activity-3-backend.md) for resource paths,
payloads, response codes, dates, ownership, and frontend integration.
The `/backend` endpoints return JSON and require session authentication and
CSRF tokens. React can call them with Inertia's `useHttp` or ordinary fetch.

Activity 4 adds the actual React pages and Inertia page rendering. Activity 5
adds the Fortify authentication interface and Spatie permissions. Those stages
are separate from the Activity 3 backend. Catalog/match management abilities
deny writes until configured; privileged access is never seeded automatically.

Development/build commands use Vite. Vite Plus 0.3.3 supplies formatting and
lint checks without its incompatible Vite package-alias requirement.

## Verification

```powershell
composer test
npm.cmd run check
npm.cmd run types:check
npm.cmd run build
npm.cmd audit
```

Backend tests use SQLite in memory. Production builds are checked separately.
MySQL deployment should run migrations and tests against its actual server
before release. The original SQL schema and demo data are reference files;
do not import the old schema into a database already managed by migrations.
