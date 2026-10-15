# Activity 5: Headless authentication and access control

## Requirements and implementation

Activity 5 on page 2 of the supplied INTECH 3112 final-project instructions requires Fortify authentication with custom React/Inertia views, Spatie roles and permissions with migrations and `HasRoles`, seeded default roles with granular permissions, and middleware protection with conditional React controls.

| Requirement                                 | Implementation                                                                                                                                                                    |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Install and configure Fortify               | Existing Composer dependency, registered `FortifyServiceProvider`, configured registration and password-reset features                                                            |
| Custom React/Inertia authentication screens | `resources/js/pages/auth/login.tsx`, `register.tsx`, `forgot-password.tsx`, and `reset-password.tsx`, registered through Fortify view callbacks                                   |
| Spatie models and database tables           | Existing package/configuration and permission migration create roles, permissions, and all three pivot tables; `User` uses `HasRoles`                                             |
| Default roles with granular permissions     | `AccessControl::ROLE_PERMISSIONS` defines the matrix; `RolesAndPermissionsSeeder` creates permissions, synchronizes roles, and assigns User to existing accounts without any role |
| Safe registration                           | Fortify validates inputs, hashes the password, assigns only User in a transaction, and rejects client-supplied role/permission/activity fields                                    |
| Backend role and permission guards          | `routes/access-control.php` applies `auth`, `active`, Spatie `role`, and `permission` middleware to real administration and skill-catalog endpoints                               |
| Conditional React controls                  | Shared Inertia `auth.roles` and `auth.permissions` determine which staff links appear in `Portal`; guests and ordinary members do not see staff actions                           |
| Runnable evidence                           | Staff CRUD, administrator role assignment, local demonstration accounts, and `ActivityFiveAccessControlTest` exercise the implementation                                          |

The packages, migrations, trait, and authentication screens were already present in the latest checkout. This activity completes the missing permission assignments, default registration role, active-account checks, guarded staff workflows, shared access props, and regression tests. No additional package installation was necessary.

## Role and permission matrix

| Spatie role | Member workspace                    | Skill catalog   | Manual match resource writes | User role assignment |
| ----------- | ----------------------------------- | --------------- | ---------------------------- | -------------------- |
| User        | Yes, with existing ownership checks | No              | No                           | No                   |
| Manager     | Yes, with existing ownership checks | `manage skills` | No                           | No                   |
| Admin       | Yes, with existing ownership checks | `manage skills` | `manage job matches`         | `manage user roles`  |

Member features remain protected by authentication, account activity, validation, and resource ownership. User has no staff permissions. Staff roles do not grant unrestricted access to private member records. The existing job-match JSON resource continues to scope records to the authenticated owner even when `manage job matches` is granted. It is not a global match-management screen.

`users.role` is the existing job-portal business classification (`job_seeker`, `employer`, etc.). Spatie's `Admin`, `Manager`, and `User` roles are separate access assignments. Changing a Spatie role does not change a member's business classification or grant employer ownership of jobs.

## Staff workflows

- `/staff/skills`: Admins and Managers can search, paginate, add, edit, and remove shared skills. Web forms reuse `SkillController` validation and authorization. Removing a skill also removes its profile/job associations through the existing foreign-key cascades; the UI asks for confirmation.
- `/admin/users`: Admins can search members and assign one of the three supported access roles. The page shows the permission matrix and the current assignments. Managers and Users receive 403 even when they forge requests or manually enter the URL.
- An administrator cannot change their own role through this form, reducing accidental loss of their own administrative access. Role changes replace the target's assigned Spatie roles and are transactional; they do not expose an arbitrary permission editor.
- The skill JSON endpoints retain their existing Laravel `can('manage skills')` checks. The match-write endpoints retain `can('manage job matches')` checks. Permissions are resolved by Spatie through Laravel's authorization system.
- Effective roles and permissions are recomputed for each request. A demoted user loses staff navigation and server access on the next request.

## Local setup and demonstration

After installing the existing dependencies and running migrations:

```powershell
$env:PATH = "${PWD}\.tools;${PWD}\.tools\php;$env:PATH"
php artisan migrate
php artisan db:seed
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

On other machines, use the installed PHP/Composer runtime rather than the project-local `.tools` PATH.

| Local email           | Password   | Spatie access |
| --------------------- | ---------- | ------------- |
| `test@example.com`    | `password` | User          |
| `manager@example.com` | `password` | Manager       |
| `admin@example.com`   | `password` | Admin         |

`ActivityFiveDemoSeeder` creates staff demonstration accounts only in `local` or `testing`. It never promotes an existing account with a matching email, resets its password, or restores a role changed by an administrator. Re-running the normal seeder is idempotent and preserves existing staff assignments. The role seeder creates role definitions in other environments but never assigns Admin to existing accounts automatically.

For a walkthrough, sign in as User and confirm that staff links are absent. Sign in as Manager, open Skill catalog, and add/edit a skill; User access is absent and `/admin/users` is forbidden. Sign in as Admin, open User access, assign Manager to a separate member, then return that member to User and verify their staff access disappears. Do not use these development passwords for deployed accounts.

## Authentication and security

- Fortify handles registration, login, logout, password-reset requests, and reset-token validation; custom React forms use Inertia's `useForm` and show server validation errors.
- Inactive accounts cannot authenticate. Existing authenticated sessions cannot access protected web or JSON resources after the account becomes inactive.
- Login attempts remain throttled by email and IP. Registration cannot set privileged fields.
- All write routes use the web session/CSRF middleware. Hiding a link is a convenience; server-side role and permission middleware remain authoritative.
- Shared user data is limited to ID, name, email, and business role; password hashes, session tokens, and other account fields are not serialized into shared props.
- Local password-reset email is written to `storage/logs/laravel.log` by `MAIL_MAILER=log`. Configure a real mail transport for deployed recovery; no external mail provider is configured by this activity.

## Verification

Build assets before HTTP feature tests because the Blade entry references page chunks in the Vite manifest.

```powershell
npm.cmd run build
npm.cmd run types:check
npm.cmd run check
php artisan test
php vendor/bin/phpstan analyse --no-progress
```

`ActivityFiveAccessControlTest` covers idempotent seeding and backfill, registration and logout, forged privilege fields, invalid input, login throttling, invalid/reused password-reset tokens, guest/member/manager/admin access, skill CRUD and validation, role changes and revocation, role-plus-permission enforcement, safe shared props, inactive accounts, CSRF rejection, and production exclusion of demo accounts. The existing `JobSeekerFlowTest` covers delivery of reset notifications and a successful full recovery flow.

Verified on October 15, 2026: all 119 tests passed with 1,038 assertions, including the authentication-history regression tests; PHPStan reported zero errors; TypeScript, frontend lint/formatting, scoped PHP source formatting, and the production build passed. Live HTTP/session checks confirmed User receives 403 on both staff pages, Manager receives 200 on the catalog and 403 on user access, and Admin receives 200 on both. Automated Chrome authentication-history checks also passed. MySQL and production mail delivery were not exercised.

## Browser Back after authentication

Authentication changes now invalidate encrypted Inertia history after Fortify finishes regenerating or destroying the session. Successful Inertia sign-in, registration, sign-out, and account-deletion redirects load a fresh document, clearing React state from the previous session. App responses send private, no-store cache headers. Authentication forms also request history replacement.

Back to an old guest-only screen while signed in is checked against the current session and redirected to the dashboard. Back to a private page after sign-out requires login again; a remembered-login cookie cannot restore that signed-out session. Public pages such as the job browser remain accessible to guests.

`AuthenticationHistoryTest` covers encrypted history, authentication-boundary invalidation, fresh-document redirects, rejected cached access after logout, session/CSRF rotation, failed login, registration, account deletion, and cache headers. An isolated Chrome browser test additionally verified Back after sign-in, repeated Back after sign-out, fresh sign-in, and Back across full-document navigation, with Remember me selected and no JavaScript errors. Signed-out JSON access remained 401 during those browser checks.

To verify manually, refresh once to load the rebuilt assets, sign in, and press Back. Then browse Profile and Applications, sign out, and press Back repeatedly. The dashboard should remain the signed-in landing page when returning to old authentication screens; after sign-out, private pages must require another sign-in.

## References

- [Laravel Fortify authentication and custom views](https://laravel.com/framework/docs/13.x/fortify)
- [Spatie role and permission middleware](https://spatie.be/docs/laravel-permission/v8/basic-usage/middleware)
- [Spatie seeding and permission-cache guidance](https://spatie.be/docs/laravel-permission/v8/advanced-usage/seeding)
- [Inertia history encryption and invalidation](https://inertiajs.com/docs/v3/security/history-encryption)
