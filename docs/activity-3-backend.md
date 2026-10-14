# Activity 3 backend

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
