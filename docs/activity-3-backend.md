# Activity 3 backend

Historical contract: the JSearch integration retires manual company, job-posting,
and job-skill writes (405), and makes applications an owner-only personal tracker.
The employer marketplace behavior below describes the earlier implementation.
See [the current job-seeker flow](job-seeker-flow.md) and
[JSearch migration](jsearch-integration.md) before integrating these resources.

The Laravel database covers the 16 tables in `database/konekt_db.sql`.
Migrations use matching foreign-key types, cascading constraints, uniqueness
rules, and timestamps. Eloquent models expose explicit relationships and
fillable fields. Actor IDs, account roles, and active-account flags are set by
trusted server code rather than mass assignment.

Laravel's existing `name`, `password`, and authentication tables are retained.
`first_name`, `last_name`, `role`, and `is_active` extend the user table.
The original `password_hash` field maps to Laravel's hashed `password` field.
The domain roles are `job_seeker` and `employer`; Spatie permissions are a
separate Activity 5 concern.

The original singular `experience` and `education` table names are retained.
Existing event dates (`applied_at`, `saved_at`, `computed_at`, `sent_at`, and
`viewed_at`) are retained alongside standard creation/update timestamps.

Run `php artisan migrate` and `php artisan db:seed --class=SkillSeeder`.
The skill catalog seeder can be run repeatedly without duplicating skills.

Model and migration tests use SQLite in memory. Local development also uses
SQLite; MySQL deployment should run the migrations and checks against its own
database before release.

The schema and relationship patterns follow the
[Laravel migration documentation](https://laravel.com/docs/13.x/migrations)
and [Eloquent relationship documentation](https://laravel.com/docs/13.x/eloquent-relationships).

## Resource endpoints

All endpoints are under `/backend`, use session authentication, and retain
Laravel's web/CSRF middleware. Send `Accept: application/json`. Browser clients
must send the session cookie and CSRF token for mutations. Inactive accounts
receive 403.

Dedicated files in `routes/` register `skills`, `profiles`, `user-skills`,
`experience`, `education`, `companies`, `job-postings`, `job-skills`,
`job-applications`, `saved-jobs`, `job-matches`, `connections`, `messages`,
`endorsements`, and `profile-views`. Route names start with `backend.`.
The concrete controllers share the standard actions in `ResourceController`.
Each controller defines its own validation and access rules.

| Request                              | Behavior                                 |
| ------------------------------------ | ---------------------------------------- |
| `GET /backend/{resource}`            | Paginated records, 25 per page           |
| `GET /backend/{resource}/create`     | JSON field names for a create form       |
| `POST /backend/{resource}`           | Validate and create; 201 with the record |
| `GET /backend/{resource}/{id}`       | Retrieve a visible record                |
| `GET /backend/{resource}/{id}/edit`  | JSON record and editable field names     |
| `PUT/PATCH /backend/{resource}/{id}` | Validate provided fields and update      |
| `DELETE /backend/{resource}/{id}`    | Delete an authorized record; 204         |

Errors use Laravel's JSON responses: 401 for missing authentication, 403 for
denied actions, 404 for absent or invisible records, 409 for conflicting
records/states, and 422 with an `errors` object for validation failures.
Ownership and event timestamps come from the server. They cannot be supplied
by the client. Private records are scoped to their owner or participants.

Companies and job mutations require the domain `employer` role. Active jobs
can be read by other authenticated users. Employer company/job references are
validated against the authenticated employer. Applicants control their own
documents and withdrawal/offer acceptance; employers control review decisions
for their jobs. Closed applications cannot be reopened by the employer.

Only connection recipients can accept a request. Blocked connections cannot be
updated. Sending messages or endorsing skills requires an accepted connection.
Recipients can mark messages read but cannot edit their content. Endorsements
must refer to a skill the recipient has, and their model mutations refresh the
cached endorsement count. Bulk SQL imports/deletions bypass model events and
must rebuild cached counts.

Shared skill catalog mutations require the `manage skills` ability; job match
mutations require `manage job matches`. These abilities deny access until
configured through Laravel Gates or the Activity 5 Spatie permissions. Account
role changes are trusted administrative operations, not registration payloads.
The CRUD endpoints store match scores; automatic matchmaking is a separate
business feature.

The backend is ready for React/Inertia integration. `create` and `edit` return
JSON metadata; Activity 4 supplies the actual frontend pages. Authentication
screens and full role/permission administration belong to Activity 5.

## Frontend handoff

Use Inertia 3's [`useHttp`](https://inertiajs.com/docs/v3/the-basics/http-requests)
or ordinary `fetch`/Axios for the JSON `/backend` endpoints. These endpoints
return records and 422 errors; they do not return Inertia page responses.
Do not send them Inertia page visits through `router`, `useForm`, or `<Form>`.
Activity 4 must supply the React page components and page routes/controllers
that call `Inertia::render(...)` and use redirects for Inertia form submissions.

An authenticated session is required to exercise private CRUD from the browser.
The Activity 5 login/registration/password-reset screens and permission setup
must be integrated before authenticated end-to-end UI testing. The model and
HTTP tests exercise that backend contract without a login screen.

Send calendar dates as `YYYY-MM-DD`; convert returned ISO dates before binding
them to HTML date inputs. Both date boundaries are checked on partial updates.
Use `company.name`, `company.logo_url`, and `company.location` on job records
for job cards. The embedded company data omits its account fields.

Employer access to other applicants stops when the employer role is revoked.
A blocked connection overrides accepted duplicate/reverse connections.
Dates, identifiers, private ownership, all seven resource actions, and CSRF
rejection have regression coverage.

## Activity 3 checklist

| Requirement                                              | Implementation                                                                                                             |
| -------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| Database connection, normalized tables, keys, timestamps | SQLite environment template, migrations for all 16 domain tables, consistent foreign keys, cascade constraints, timestamps |
| Eloquent models and mass-assignment protection           | All 16 models have explicit fillable arrays; one-to-one, one-to-many, and many-to-many relationships are declared          |
| Seven CRUD actions and server validation                 | 15 resource controllers inherit the seven working actions; each supplies validated fields and access checks                |
| Dedicated files, named routes, prefixes, groups          | 105 resource routes in dedicated files, grouped under `/backend` with `backend.*` names                                    |

The `users` account lifecycle belongs to Fortify/Activity 5 rather than a public
user-management CRUD endpoint. `create`/`edit` are JSON form metadata during
Activity 3, ready to be connected to Activity 4 pages.
