# Run the Philippine job-search branch

Use this guide for **`feat/jsearch-philippines`** in
[vylophil/KoneKTLV](https://github.com/vylophil/KoneKTLV).

This branch contains real Philippine job searches, saved jobs, external
application links and a personal application tracker. It needs **JSearch**, PHP,
Composer and Node.js. **OmniRoute, an AI provider and an OpenAI key are not
required.** Resume matching on this branch uses local keywords; AI matching and
profile autofill are on a separate branch.

Commands below use Windows PowerShell. Use `npm.cmd` to avoid PowerShell's
`npm.ps1` execution-policy issue. On macOS/Linux use `npm` instead.

## 1. Get the correct code

For a new checkout:

```powershell
git clone --branch feat/jsearch-philippines https://github.com/vylophil/KoneKTLV.git KoneKTLV
Set-Location KoneKTLV
```

For an existing checkout, first commit or otherwise preserve your own changes:

```powershell
git status --short
git fetch origin
git switch feat/jsearch-philippines
git pull --ff-only origin feat/jsearch-philippines
```

Check the result:

```powershell
git branch --show-current
git log -3 --oneline
```

The branch should be `feat/jsearch-philippines`, and its history should include
`2fa760b` (Philippine JSearch integration). Updating only `main` does not bring in
this feature until the branch is merged there.

If GitHub denies access, sign in through Git Credential Manager or use your
configured SSH access, and ask the owner to grant repository access. Do not put
tokens in clone URLs or share credentials in chat.

## 2. Check prerequisites

Install these on the developer's computer:

- PHP **8.4 recommended**, with Laravel's required extensions, cURL and PDO SQLite.
- Composer 2.
- Node.js 22 LTS, at least 22.12; Node 22.23.2 is the verified local version.
- Git.

```powershell
php -v
composer --version
node --version
npm.cmd --version
```

The owner's portable `.tools/php` runtime is ignored by Git. A teammate's clone
does not receive it. Install PHP/Composer normally; do not depend on
`scripts/activate.ps1` unless that checkout actually has the portable runtime.
Make sure PATH selects the supported PHP version rather than an older XAMPP PHP.

## 3. Install and initialize a fresh checkout

Run in the project root, in order. Stop and resolve any failed command:

```powershell
composer install
composer check-platform-reqs
npm.cmd ci --ignore-scripts
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File | Out-Null }
php artisan migrate
php artisan db:seed
```

`key:generate` is for a **new local environment**. Keep an existing `APP_KEY`
when updating an environment that already contains data. Migrations create the
SQLite tables, including the external-listing fields and API usage counter.
There is no need to import the legacy SQL file or create employer accounts.

For an existing configured checkout, preserve `.env`, the application key and
database; install dependencies and run `php artisan migrate` instead of
reinitializing everything. Back up an existing database before migrating.

## 4. Add the JSearch key

1. Sign in to the team's authorized OpenWeb Ninja account, or create your own
   authorized account, and obtain a key with access to JSearch.
2. Open the local `.env` file in your editor.
3. Set the following values, filling the blank key locally:

    ```dotenv
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://127.0.0.1:8000
    DB_CONNECTION=sqlite
    SESSION_DRIVER=database
    CACHE_STORE=database

    JSEARCH_API_KEY=
    JSEARCH_MONTHLY_LIMIT=180
    ```

4. Preserve the generated `APP_KEY`. Leave `DB_DATABASE` unset for the default
   `database/database.sqlite`, or point it to this checkout's SQLite file.
5. Remove `JSEARCH_ENABLED=false` if you previously disabled live search. A
   non-empty key otherwise enables it automatically.
6. Apply configuration:

    ```powershell
    php artisan optimize:clear
    ```

Keep the key in `.env`; that file is ignored by Git. Do not use a `VITE_` key,
commit it to source, or put it into a browser request. The Laravel server makes
the provider request.

The default limit is 180 uncached single-page requests per calendar month **per
application database**. The provider's actual account quota still applies.
Several developers sharing a key share that provider quota, even when each has
a separate local database/counter. Coordinate testing and check the provider's
dashboard for your current plan and remaining allowance.

## 5. Build and start KoneKT

```powershell
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open <http://127.0.0.1:8000/jobs> and leave the Laravel terminal running.

PHP must remain on PATH during the frontend build because Wayfinder invokes
Artisan to generate route/action code. For this stable local setup, you do not
need another frontend server. To develop with hot reload, use a second terminal
in this same checkout and run `npm.cmd run dev`.

After later frontend changes, run `npm.cmd run build` again and refresh the page.
Pulling code alone does not rebuild the interface.

## 6. Confirm that job search works

1. On **Find jobs**, search for `software developer` and location `Manila`.
2. Confirm that listings identify real source companies/publishers and show a
   last-retrieved time. The integration requests Philippine jobs.
3. Open a listing and inspect its complete description and source application link.
4. Try work-arrangement/job-type filters and the next-results control when
   supplied by the provider.
5. Revisit the homepage. It previews already-retrieved listings; it does not make
   a separate job-source request by itself.

Identical searches share a six-hour cache. The source returns up to ten records
per page and a cursor for another page; KoneKT does not invent an overall result
count. A new query, different filters or another provider page can spend another
request. Do not clear the cache repeatedly just to test a search.

Without a key, the local seeder's labeled demo jobs can exercise the UI, but they
do not prove live JSearch connectivity. Each teammate's fresh database starts
without the owner's imported listings.

## 7. Test saved jobs and the application tracker

After local seeding, sign in at `/login` using:

| Field    | Value              |
| -------- | ------------------ |
| Email    | `test@example.com` |
| Password | `password`         |

These are local demonstration credentials. You can also register your own account.

1. Save a real listing and confirm it appears under **Saved jobs**.
2. Select **Apply on source site**. The original site handles the application;
   KoneKT does not send your resume to an employer.
3. Check **Applications**. Opening a link is recorded as **Link opened**.
4. After applying on the external site, select **I've applied** in KoneKT.
5. Change the personal tracker status and add private notes.

Tracker statuses are self-reported. Opening a link is not a confirmed submission.
There is no employer posting/login requirement for imported jobs.

Local Admin/Manager demonstration accounts, if needed for staff controls, use
`admin@example.com` and `manager@example.com`, with password `password`. They use
the same `/login` form. Staff roles do not make someone an employer, and these
demo accounts are not created in production.

## 8. Optional: use a worktree without disturbing other work

From an existing repository with the remote feature branch fetched:

```powershell
git fetch origin
git worktree add -b work/alex-jsearch "../KoneKTLV-jsearch" origin/feat/jsearch-philippines
Set-Location "../KoneKTLV-jsearch"
```

Choose your own new task-branch name instead of the example `work/alex-jsearch`.
Do not check out a branch already active in another worktree.

Repeat sections 2-5 in the new directory. Dependencies, `.env`, SQLite, build
assets and private files are separate. Use the new worktree's own database and
application key. If another checkout is already running, set these differences
in the worktree's `.env`:

```dotenv
APP_URL=http://127.0.0.1:8001
SESSION_COOKIE=konekt-jsearch-session
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```

Then run:

```powershell
php artisan optimize:clear
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8001
```

Use <http://127.0.0.1:8001/jobs>. Cookies are not isolated by port, so give each
simultaneous checkout a different `SESSION_COOKIE`. If using Vite in both, give
the second one a separate port, for example:

```powershell
npm.cmd run dev -- --host=127.0.0.1 --port=5174 --strictPort
```

Do not share the two worktrees' database, storage, `public/build`, `public/hot`
or dependency folders. The JSearch provider quota is still shared if both use
the same provider key.

## 9. Run verification

Run in order. Finish the build before running feature tests:

```powershell
npm.cmd run types:check
npm.cmd run check
npm.cmd run build
composer test
```

For a focused provider integration check:

```powershell
php artisan test --filter=JSearchTest
```

Automated tests use fake HTTP responses and disable live JSearch by default;
they do not consume real API credits. Live connectivity is verified by the
browser search in section 6. MySQL deployment requires separate testing against
the actual MySQL server; the default tests use SQLite in memory.

## 10. Troubleshooting and branch limits

| Symptom                               | Action                                                                                                                                                      |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Old UI or missing feature             | Verify the branch, migrate, rebuild and refresh; only pulling `main` does not include this branch yet                                                       |
| Portable PHP activation fails         | Install PHP/Composer yourself; `.tools` is not shared through Git                                                                                           |
| Wrong PHP version                     | Check `php -v` and PATH; use the supported runtime and Composer platform check                                                                              |
| Database/table error                  | Verify the correct SQLite file exists and run migrations                                                                                                    |
| Only demo jobs                        | Configure a working JSearch key and clear Laravel configuration                                                                                             |
| Empty homepage                        | Search on `/jobs` first so listings are imported                                                                                                            |
| Provider/quota failure                | Check the account key, JSearch access and real provider allowance; avoid repeated searches while unavailable                                                |
| Need stored listings during an outage | Open `/jobs?cached=1`; a fresh local database may not have any yet                                                                                          |
| Monthly application limit reached     | The counter is in the database; clearing caches does not reset it                                                                                           |
| Live search unexpectedly disabled     | Remove `JSEARCH_ENABLED=false`, verify the key, then clear config                                                                                           |
| Salary not disclosed                  | This branch uses the provider's structured salary/text fields; a description can still mention pay when those fields are empty, so inspect the full listing |
| AI/autofill controls absent           | Expected: this job-search-only branch does not contain OmniRoute/AI features                                                                                |
| Tests report missing Vite/font files  | Finish the build first; do not run it simultaneously with tests                                                                                             |

The later description-salary fallback and AI/resume improvements are separate
commits on `feat/omniroute-matching`; this guide does not claim they are included
in the job-search-only release.

For a shared deployment, host Laravel with the compiled assets, server-side
JSearch credentials, a persistent database and persistent private file storage.
Teammates using that hosted website only need its URL and login. Never expose
your local `.env`, use the demo password publicly, or deploy the local SQLite
database containing developer/test accounts as the production database.

Further details: [JSearch integration contract](jsearch-integration.md),
[jobseeker flow](job-seeker-flow.md), [project overview](../README.md).
