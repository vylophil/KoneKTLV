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
