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
